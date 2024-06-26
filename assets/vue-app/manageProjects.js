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
        messageError: null,
        showModal: false,
        uploadPercent: 0,
        sittingMaxSize: 0,
        maxGenerationSize: 0,
        fileMaxSize: 0,
        projectFilesTooBig: false,
        documentFilesTooBig: false,
        sittingTooBigForCreation: false,
        sittingTooBigForGeneration: false,
        projectFilesSize: 0,
        otherDocsFilesSize: 0,
        totalAllFileSize: 0,

        coefficientCorrecteur: 1.17647058824,
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
        annexChange(event) {
            isDirty = true;
        },

        errorMsgTitleTooLong(event) {
            const errorValidation = '<small class="text-danger ps-3"><span class="fa fa-exclamation-circle me-2"></span>Le libellé ne doit pas dépasser 512 caractères</small>'
            let element = event.target.parentElement;
            if (event){
                if (isTooLong(event.target.value, 512)) {
                    this.$refs.submitBtn.setAttribute('disabled', 'disabled');
                    !element.querySelector('.text-danger') ? element.insertAdjacentHTML('beforeend', errorValidation): null;
                    return;
                }
                element.querySelector('.text-danger') ? element.querySelector('.text-danger').remove() : null;
                this.$refs.submitBtn.removeAttribute('disabled');
            }
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
                    size:Math.round(file.size * this.coefficientCorrecteur)
                };
                project.file.size > this.fileMaxSize ? this.showMessageError(`La taille du fichier ne doit pas dépasser les 200Mo. Taille actuelle de votre ficher : ${this.formatSize(project.file.size)}`) :
                this.projects.push(project);
            }
            this.projectFilesSize = getProjectsFilesWeight(this.projects);
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.projectFilesTooBig = isOverWeight(this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.sittingMaxSize);
            isDirty = true;

        },

        removeProject(index) {
            this.projects.splice(index, 1);
            this.projectFilesSize = getProjectsFilesWeight(this.projects)
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.projectFilesTooBig = isOverWeight(this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.sittingMaxSize);
            isDirty = true;
        },

        addAnnexes(event, project) {
            for (let i = 0; i < event.target.files.length; i++) {
                let file = event.target.files[i];
                let annex = {
                    title: getPrettyNameFromFileName(file.name),
                    file: file,
                    linkedFileKey: null,
                    fileName: file.name,
                    id: null,
                    size:Math.round(file.size * this.coefficientCorrecteur)
                };
                annex.file.size > this.fileMaxSize ? this.showMessageError(`La taille du fichier ne doit pas dépasser les 200Mo. Taille actuelle de votre ficher : ${this.formatSize(annex.file.size)}`):
                project.annexes.push(annex);
            }
            this.projectFilesSize = getProjectsFilesWeight(this.projects)
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.projectFilesTooBig = isOverWeight(this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.sittingMaxSize);

            isDirty = true;
        },

        deleteAnnex(annexes, index) {
            annexes.splice(index, 1);
            this.projectFilesSize = getProjectsFilesWeight(this.projects)
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.projectFilesTooBig = isOverWeight(this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.sittingMaxSize);
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
                    size:Math.round(file.size * this.coefficientCorrecteur)
                };
                otherdoc.file.size > this.fileMaxSize ? this.showMessageError(`La taille du fichier ne doit pas dépasser les 200Mo. Taille actuelle de votre ficher : ${this.formatSize(otherdoc.file.size)}`) :
                this.otherdocs.push(otherdoc);
            }
            this.otherDocsFilesSize = getOtherdocsFilesWeight(this.otherdocs);
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.documentFilesTooBig = isOverWeight(this.otherDocsFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.sittingMaxSize);
            isDirty = true;
        },

        removeOtherdoc(index) {
            this.otherdocs.splice(index, 1);
            this.otherDocsFilesSize = getOtherdocsFilesWeight(this.otherdocs)
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.documentFilesTooBig = isOverWeight(this.otherDocsFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.otherDocsFilesSize + this.projectFilesSize, this.sittingMaxSize);
            isDirty = true;
        },

        save() {

            if(isOverWeight(this.totalAllFileSize, this.sittingMaxSize)) {
                this.sittingTooBigForCreation = true
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
                    },1000);

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
            setTimeout(() => this.messageError = null, 5000);
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
            this.projectFilesSize = getProjectsFilesWeight(this.projects);
            this.otherDocsFilesSize = getOtherdocsFilesWeight(this.otherdocs);
            this.totalAllFileSize = getAllFilesWeight(this.otherDocsFilesSize, this.projectFilesSize);
            this.projectFilesTooBig = isOverWeight(this.projectFilesSize, this.maxGenerationSize);
            this.documentFilesTooBig = isOverWeight(this.otherDocsFilesSize, this.maxGenerationSize);
            this.sittingTooBigForGeneration = isOverWeight(this.totalAllFileSize, this.maxGenerationSize);
            this.sittingTooBigForCreation = isOverWeight(this.totalAllFileSize, this.sittingMaxSize)
        });

    }
});


function isTooLong(title, max) {
    return title.length > max;
}

function isOverWeight(current, limit) {
    return current > limit;
}

function addProjectAndAnnexeFiles(projects, formData) {
    for (let i = 0; i < projects.length; i++) {
        if (isNewProject(projects[i])) {
            formData.append(`projet_${i}_rapport`,
                projects[i].file,
                projects[i].file.name,
                projects[i].size
            );

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
            formData.append(`projet_${index}_${j}_annexe`,
                project.annexes[j].file,
                project.annexes[j].file.name,
                project.annexes[j].size,
                project.annexes[j].title
            );

            project.annexes[j].linkedFileKey = `projet_${index}_${j}_annexe`;
        }
    }
}

function addOtherdocFiles(otherdocs, formDataDocs) {
    for (let k = 0; k < otherdocs.length; k++) {
        if (isNewOtherdoc(otherdocs[k])) {
            formDataDocs.append(`autre_${k}_rapport`,
                otherdocs[k].file,
                otherdocs[k].file.name,
                otherdocs[k].size
            );
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

function getProjectsFilesWeight(projects) {
    let projectFilesSize = 0;

    for (let project of projects) {
        projectFilesSize += project.size;
        for (let annexe of project.annexes) {
            projectFilesSize += annexe.size
        }
    }
    return projectFilesSize
}

function getOtherdocsFilesWeight(otherdocs) {
    let otherDocsFilesSize = 0

    for (let otherdoc of otherdocs) {
        otherDocsFilesSize += otherdoc.size
    }
    return otherDocsFilesSize ;
}

function getAllFilesWeight(getOTrherdocsFilesWeight, getProjectsFilesWeight) {
    return getProjectsFilesWeight + getOTrherdocsFilesWeight;
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
