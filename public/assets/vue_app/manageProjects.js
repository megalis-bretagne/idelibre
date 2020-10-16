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
                };
                project.annexes.push(annex);
            }
        },
        deleteAnnex(annexes, index) {
            annexes.splice(index, 1);
        },

        save() {
            let formData = new FormData();
            formData.append('projects', JSON.stringify(this.projects));

            for(let i = 0; i< this.projects.length; i++){
                formData.append(`projet_${i}_rapport`, this.projects[i].file, this.projects[i].file.name );
                for(let j = 0; j< this.projects[i].annexes.length; j++){
                    formData.append(`projet_${i}_${j}_annexe`, this.projects[i].annexes[j].file, this.projects[i].annexes[j].file.name);
                }
            }

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

function formatData() {

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
