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
        otherdocs: [],
        themes: [],
        reporters: [],
        messageInfo: null,
        showModal: false,
        uploadPercent: 0,
        messageError: null,
        fileTooBig: false,
        sittingTooBig: false,
        sittingMaxSize: 0,
        maxGenerationSize: 0,
        fileMaxSize: 0,
        totalFileSize: 0,
        otherdocsTotalFileSize: 0,
        totalSittingSize: 0

    },


    methods: {

        formatSize(value) {
            return formatBytes(value);
        },

        reporterFilter(option, label, search) {
            let searchLowerCase = search.toLowerCase();
            return option.firstName.toLowerCase().indexOf(searchLowerCase) > -1 ||
                option.lastName.toLowerCase().indexOf(searchLowerCase) > -1 ||
                (option.firstName.toLowerCase() + " " + option.lastName.toLowerCase()).indexOf(searchLowerCase) > -1
        },

        projectChange(event) {
            isDirty = true;
        },
        otherdocChange(event) {
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
                    id: null,
                    size:event.target.files[i].size,
                };
                project.file.size > this.fileMaxSize ? this.showMessageError(`La taille du fichier ne doit pas dépasser les 200Mo. Taille actuelle de votre ficher : ${this.formatSize(project.file.size)}`) :
                this.projects.push(project);
            }
            this.totalFileSize = getFilesWeight(this.projects);
            this.totalFileSize > this.maxGenerationSize ? this.fileTooBig = true: this.fileTooBig = false ;

            if(this.sittingMaxSize <  this.totalFileSize + this.otherdocsTotalFileSize){
                this.showMessageError("Le poids de la séance dépasse les 2Go, elle ne pourra pas être enregistrée. Veuillez réduire le poids de vos pdfs.")
                document.querySelector('#save-sitting').disabled = true
            }

            isDirty = true;

        },

        removeProject(index) {
            this.projects.splice(index, 1);
            this.totalFileSize = getFilesWeight(this.projects)
            this.totalFileSize > this.maxGenerationSize ? this.fileTooBig = true: this.fileTooBig = false ;
            this.totalFileSize + this.otherdocsTotalFileSize < this.sittingMaxSize ? document.querySelector('#save-sitting').disabled = false : document.querySelector('#save-sitting').disabled = true;
            isDirty = true;
        },

        addAnnexes(event, project) {
            for (let i = 0; i < event.target.files.length; i++) {
                let file = event.target.files[i];
                let annex = {
                    file: file,
                    linkedFileKey: null,
                    fileName: file.name,
                    id: null,
                    size:event.target.files[i].size
                };
                annex.file.size > this.fileMaxSize ? this.showMessageError(`La taille du fichier ne doit pas dépasser les 200Mo. Taille actuelle de votre ficher : ${this.formatSize(annex.file.size)}`):
                project.annexes.push(annex);
            }
            this.totalFileSize = getFilesWeight(this.projects)
            this.totalFileSize > this.maxGenerationSize ? this.fileTooBig = true: this.fileTooBig = false ;

            if(this.sittingMaxSize <  this.totalFileSize + this.otherdocsTotalFileSize){
                this.showMessageError("Le poids de la séance dépasse les 2Go, elle ne pourra pas être enregistrée. Veuillez réduire le poids de vos pdfs.")
                document.querySelector('#save-sitting').disabled = true
            }

            isDirty = true;
        },

        deleteAnnex(annexes, index) {
            annexes.splice(index, 1);
            this.totalFileSize = getFilesWeight(this.projects)
            this.totalFileSize > this.maxGenerationSize ? this.fileTooBig = true: this.fileTooBig = false ;
            this.totalFileSize + this.otherdocsTotalFileSize < this.sittingMaxSize ? document.querySelector('#save-sitting').disabled = false : document.querySelector('#save-sitting').disabled = true;
            isDirty = true;
        },

        addOtherdoc(event) {
            for (let i = 0; i < event.target.files.length; i++) {
                let file = event.target.files[i];
                let otherdoc = {
                    name: getPrettyNameFromFileName(file.name),
                    fileName: file.name,
                    file: file,
                    linkedFileKey: null,
                    id: null,
                    size:event.target.files[i].size,
                };
                otherdoc.file.size > this.fileMaxSize ? this.showMessageError(`La taille du fichier ne doit pas dépasser les 200Mo. Taille actuelle de votre ficher : ${this.formatSize(otherdoc.file.size)}`) :
                this.otherdocs.push(otherdoc);
            }
            this.otherdocsTotalFileSize = getOtherdocsFilesWeight(this.otherdocs)

            if(this.sittingMaxSize <  this.totalFileSize + this.otherdocsTotalFileSize){
                this.showMessageError("Le poids de la séance dépasse les 2Go, elle ne pourra pas être enregistrée. Veuillez réduire le poids de vos pdfs.")
                document.querySelector('#save-sitting').disabled = true
            }

            isDirty = true;
        },

        removeOtherdoc(index) {
            this.otherdocs.splice(index, 1);
            this.otherdocsTotalFileSize = getOtherdocsFilesWeight(this.otherdocs)
            this.totalFileSize + this.otherdocsTotalFileSize < this.sittingMaxSize ? document.querySelector('#save-sitting').disabled = false : document.querySelector('#save-sitting').disabled = true;
            isDirty = true;
        },

        save() {

            if(!checkNotOverweightSitting(this.totalSittingSize, this.sittingMaxSize)) {
                this.sittingTooBig = true
                this.showMessageError("Le poids de la séance dépasse les 2Go, elle ne pourra pas être enregistrée. Veuillez réduire le poids de vos pdfs.")
            }

            let formData = new FormData();
            addProjectAndAnnexeFiles(this.projects, formData);
            setProjectsRank(this.projects);
            formData.append('projects', JSON.stringify(this.projects));

            let formDataDocs = new FormData();
            addOtherdocFiles(this.otherdocs, formDataDocs);
            setOtherdocsRank(this.otherdocs);
            formDataDocs.append('otherdocs', JSON.stringify(this.otherdocs));


            this.showModal = true;
            this.uploadPercent = 0;
            const config = {
                onUploadProgress: (progressEvent) => {
                    this.uploadPercent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            }

            Promise
                .all([
                    axios.post(`/api/projects/${getSittingId()}`,
                        formData,
                        config
                    ),
                    axios.post(`/api/otherdocs/${getSittingId()}`,
                        formDataDocs,
                        config
                    )
                ])
                .then(( response) => {
                    console.log('done');
                    this.showMessage('Modifications enregistrées');
                    isDirty = false;
                    this.showModal = false;
                    window.scrollTo(0, 0);
                    setTimeout(function(){
                        window.location.href = `/sitting/show/${getSittingId()}/projects`
                    },
                        1000);
                    ;
                })
                .catch((e, m) => {
                    this.showModal = false;
                    window.scrollTo(0, 0);
                    let errorBody = e.response.data;
                    this.showMessageError(errorBody.message ? errorBody.message : 'Impossible d\'enregistrer les modifications');
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
            axios.get(`/api/otherdocs/${getSittingId()}`).then((response) => {
                this.otherdocs = response.data;
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
            window.scrollTo(0, 0);
            this.messageError = msg;
            setTimeout(() => this.messageError = null, 10000);
        },
    },

    mounted() {
        Promise.all([
            axios.get('/api/themes'),
            axios.get('/api/actors'),
            axios.get(`/api/projects/${getSittingId()}`),
            axios.get(`/api/otherdocs/${getSittingId()}`),
            axios.get('/api/sittings/maxGenerationSize'),
            axios.get('/api/sittings/fileMaxSize'),
            axios.get('/api/sittings/sittingMaxSize'),
        ]).then((response) => {
            this.themes = setThemeLevelName(response[0].data);
            this.reporters = response[1].data;
            this.projects = response[2].data;
            this.otherdocs = response[3].data;
            this.maxGenerationSize = response[4].data.maxGenerationSize;
            this.fileMaxSize = response[5].data.fileMaxSize;
            this.sittingMaxSize = response[6].data.sittingMaxSize;
            this.totalFileSize = getFilesWeight(this.projects);
            this.otherdocsTotalFileSize = getOtherdocsFilesWeight(this.otherdocs);
            this.totalSittingSize = this.totalFileSize + this.otherdocsTotalFileSize;
            this.fileTooBig = getFileTooBig(this.totalFileSize, this.maxGenerationSize);
            this.sittingTooBig = getSittingTooBig(this.totalSittingSize, this.sittingMaxSize)
        });
    }
});


function getFileTooBig(totalFileSize, maxGenerationSize) {
    return !checkNotOverweightFile(totalFileSize, maxGenerationSize);
}
function getSittingTooBig(totalSittingSize, sittingMaxSize) {
    return  !checkNotOverweightSitting(totalSittingSize, sittingMaxSize)
}

function addProjectAndAnnexeFiles(projects, formData) {
    for (let i = 0; i < projects.length; i++) {
        if (isNewProject(projects[i])) {
            formData.append(`projet_${i}_rapport`, projects[i].file, projects[i].file.name, projects[i].size);
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
        if (isNewAnnex(project.annexes[j]) ) {
            formData.append(`projet_${index}_${j}_annexe`, project.annexes[j].file, project.annexes[j].file.name, project.annexes[j].size);
            project.annexes[j].linkedFileKey = `projet_${index}_${j}_annexe`;
        }
    }
}

function addOtherdocFiles(otherdocs, formDataDocs) {
    for (let k = 0; k < otherdocs.length; k++) {
        if (isNewOtherdoc(otherdocs[k])) {
            formDataDocs.append(`autre_${k}_rapport`, otherdocs[k].file, otherdocs[k].file.name, otherdocs[k].size);
            otherdocs[k].linkedFileKey = `autre_${k}_rapport`;
        }
    }
}

function formatBytes(a, b = 0) {
    if (!+a)
        return "0";
    const c = 0 > b ? 0 : b, d = Math.floor(Math.log(a) / Math.log(1000));
    return `${parseFloat((a / Math.pow(1000, d)).toFixed(c))} ${["Octets", "Ko", "Mo", "Go"][d]}`
}

function getFilesWeight(projects) {
    let totalFileSize = 0;
    for (let project of projects) {
        totalFileSize += project.size;
        for (let annexe of project.annexes) {
            totalFileSize += annexe.size
        }
    }
    return totalFileSize;
}

function getOtherdocsFilesWeight(otherdocs) {
    let otherdocsTotalFileSize = 0

    for (let otherdoc of otherdocs) {
        otherdocsTotalFileSize += otherdoc.size
    }
    return otherdocsTotalFileSize;
}


function  checkNotOverweightFile(totalFileSize, maxGenerationSize) {
    return maxGenerationSize > totalFileSize;
}
function  checkNotOverweightSitting(totalSittingSize , sittingMaxSize) {
    return  sittingMaxSize > totalSittingSize;
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

function isNewOtherdoc(otherdoc) {
    return !otherdoc.id;
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

function setOtherdocsRank(otherdocs) {
    for (let i = 0; i < otherdocs.length; i++) {
        otherdocs[i].rank = i;
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

document.addEventListener('DOMContentLoaded', function () {
    window.onscroll = function (ev) {
        document.getElementById("cRetour").className = (window.pageYOffset > 100) ? "cVisible" : "cInvisible";
    };
    $('#cRetour').click(function () {
        $('html,body').animate({scrollTop: 0}, 'slow');
    });
});
