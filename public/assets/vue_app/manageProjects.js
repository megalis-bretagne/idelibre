Vue.component('v-select', VueSelect.VueSelect);

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        projects: [],
        themes: [],
        rapporteurs: [],
    },

    methods: {
        addProject(event) {
            for (let i = 0; i < event.target.files.length; i++) {
                let file = event.target.files[i];
                let project = {
                    name: getPrettyNameFromFileName(file.name),
                    file: file,
                    annexes: [],
                    themeId: null,
                    rapporteurId: null,
                    linkedFile: null
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
                    linkedFile: null
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
            formData.append('projects', JSON.stringify(this.projects));

            axios.post('/api/themes', formData).then(response => {
                console.log(response);
            });
        },


    },
    mounted() {
        Promise.all([
            axios.get('/api/themes'),
            axios.get('/api/actors'),
        ]).then((response) => {
            this.themes = setThemeLevelName(response[0].data);
            this.rapporteurs = response[1].data
        });
    }
});

function addProjectAndAnnexeFiles(projects, formData) {
    for (let i = 0; i < projects.length; i++) {
        formData.append(`projet_${i}_rapport`, projects[i].file, projects[i].file.name);
        projects[i].linkedFile = `projet_${i}_rapport`;
        addAnnexeFiles(projects[i], i, formData);
    }
}

function addAnnexeFiles(project, index, formData) {
    if (!project.annexes) {
        return;
    }
    for (let j = 0; j < project.annexes.length; j++) {
        formData.append(`projet_${index}_${j}_annexe`, project.annexes[j].file, project.annexes[j].file.name);
        project.annexes[j].linkedFile = `projet_${index}_${j}_annexe`;
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

