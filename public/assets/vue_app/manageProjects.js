Vue.component('v-select', VueSelect.VueSelect);

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        projects: [],
        themes: [],
        reporters: [],
    },

    methods: {
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
        },

        removeProject(index) {
            this.projects.splice(index, 1);
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
        },
        deleteAnnex(annexes, index) {
            annexes.splice(index, 1);
        },

        save() {
            let formData = new FormData();
            addProjectAndAnnexeFiles(this.projects, formData);
            setProjectsRank(this.projects);
            formData.append('projects', JSON.stringify(this.projects));

            axios.post(`/api/projects/${getSittingId()}`, formData).then(response => {
                console.log(response);
            });
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
});


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
    return window.location.pathname.split('/')[2];
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
