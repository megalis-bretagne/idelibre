import './vue-app.css';

import Vue from 'vue/dist/vue';
import axios from 'axios';
import VueSelect from 'vue-select';
import $ from 'jquery';
import 'bootstrap';
import 'sortablejs';
import draggable from 'vuedraggable';

Vue.component('v-select', VueSelect);
Vue.component('draggable', draggable);

let app = new Vue({
        delimiters: ['${', '}'],
        el: "#app",
        data: {
            projects: [],
            themes: [],
            reporters: [],
            messageInfo: null,
            showModal: false,
            uploadPercent: 0,
            messageError: null,

        },

        methods: {

            reporterFilter(option, label, search) {
                let searchLowerCase = search.toLowerCase();
                return option.firstName.toLowerCase().indexOf(searchLowerCase) > -1 ||
                    option.lastName.toLowerCase().indexOf(searchLowerCase) > -1 ||
                    (option.firstName.toLowerCase() + " " + option.lastName.toLowerCase()).indexOf(searchLowerCase) > -1
            },


            projectChange($event) {
                isDirty = true;
            },


            addProject(event) {
                for (let i = 0; i < event.target.files.length; i++) {
                    let file = event.target.files[i];
                    let project = {
                        name: getPrettyNameFromFileName(file.name),
                        fileName: file.name,
                        file: file,
                        annexes: [],
                        themeId: null,
                        reporterId: null,
                        linkedFileKey: null,
                        id: null
                    };
                    this.projects.push(project);
                }
                isDirty = true;
            },

            removeProject(index) {
                this.projects.splice(index, 1);
                isDirty = true;
            },
            addAnnexes(event, project) {
                for (let i = 0; i < event.target.files.length; i++) {
                    let file = event.target.files[i];
                    let annex = {
                        file: file,
                        linkedFileKey: null,
                        fileName: file.name,
                        id: null
                    };
                    project.annexes.push(annex);
                }
                isDirty = true;
            },
            deleteAnnex(annexes, index) {
                annexes.splice(index, 1);
                isDirty = true;
            },

            save() {
                let formData = new FormData();
                addProjectAndAnnexeFiles(this.projects, formData);
                setProjectsRank(this.projects);
                formData.append('projects', JSON.stringify(this.projects));
                this.showModal = true;
                this.uploadPercent = 0;
                const config = {
                    onUploadProgress: (progressEvent) => {
                        this.uploadPercent = Math.round((progressEvent.loaded * 100) / progressEvent.total);

                    }
                }

                axios.post(`/api/projects/${getSittingId()}`,
                    formData,
                    config
                ).then(response => {
                    console.log('done');
                    this.showMessage('Modifications enregistrées');
                    isDirty = false;
                    this.showModal = false;
                    window.scrollTo(0, 0);
                }).catch((e, m) => {
                    this.showModal = false;
                    window.scrollTo(0, 0);
                    console.log(e.response.data)
                    let errorBody = e.response.data;
                    this.showMessageError(errorBody.message ? errorBody.message :'Impossible d\'enregistrer les modifications');
                });
            },

            move(fromIndex, toIndex) {
                arrayMove(this.projects, fromIndex, toIndex);
            },


            cancel() {
                axios.get(`/api/projects/${getSittingId()}`).then((response) => {
                    this.projects = response.data;
                    isDirty = false;
                    this.showMessage('Modifications annulées');
                    window.scrollTo(0, 0);
                })
            },

            showMessage(msg) {
                this.messageInfo = msg;
                setTimeout(() => this.messageInfo = null, 3000);
            },

            showMessageError(msg) {
                this.messageError = msg;
                setTimeout(() => this.messageError = null, 3000);
            },


        },
        mounted() {
            Promise.all([
                axios.get('/api/themes'),
                axios.get('/api/actors'),
                axios.get(`/api/projects/${getSittingId()}`)
            ]).then((response) => {
                this.themes = setThemeLevelName(response[0].data);
                this.reporters = response[1].data;
                this.projects = response[2].data;
            });
        }
    })
;


function addProjectAndAnnexeFiles(projects, formData) {
    for (let i = 0; i < projects.length; i++) {
        if (isNewProject(projects[i])) {
            formData.append(`projet_${i}_rapport`, projects[i].file, projects[i].file.name);
            projects[i].linkedFileKey = `projet_${i}_rapport`;
        }
        addAnnexeFiles(projects[i], i, formData);
    }
}

function addAnnexeFiles(project, index, formData) {
    if (!project.annexes) {
        return;
    }
    for (let j = 0; j < project.annexes.length; j++) {
        if (isNewAnnex(project.annexes[j]))
            formData.append(`projet_${index}_${j}_annexe`, project.annexes[j].file, project.annexes[j].file.name);
        project.annexes[j].linkedFileKey = `projet_${index}_${j}_annexe`;
    }
}

function getPrettyNameFromFileName(fileName) {
    return fileName.replace(/\.[^/.]+$/, "").replace(/_/g, " ");
}


function arrayMove(arr, fromIndex, toIndex) {
    let element = arr[fromIndex];
    arr.splice(fromIndex, 1);
    arr.splice(toIndex, 0, element);
}

function setThemeLevelName(themes) {
    for (let i = 0; i < themes.length; i++) {
        themes[i].levelName = '';
        for (let j = 1; j < themes[i].lvl; j++) {
            themes[i].levelName += '--'
        }
        themes[i].levelName += themes[i].name;
    }
    return themes;
}

function getSittingId() {
    return window.location.pathname.split('/')[3];
}

function isNewProject(project) {
    return !project.id;
}

function isNewAnnex(annex) {
    return !annex.id;
}

function setProjectsRank(projects) {
    for (let i = 0; i < projects.length; i++) {
        projects[i].rank = i;
        setAnnexesRank(projects[i].annexes)
    }

}


function setAnnexesRank(annexes) {
    for (let i = 0; i < annexes.length; i++) {
        annexes[i].rank = i;
    }
}


let isDirty = false;


// OUi faut pas laisser ça comme ça
//TODO tout faire en vuejs comme cela on eite cette merde la

$('.change-tab').click(function (event) {

    if ($(this).hasClass('active')) {
        event.preventDefault();
        return false;
    }

    if (isDirty) {
        event.preventDefault();
        $('#confirm-btn').attr('href', $(this).attr('href'));
        $('#confirm-not-save').modal();

        return false;
    }
    return true;
});
