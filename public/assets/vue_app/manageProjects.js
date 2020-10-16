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
            formatData();
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
    console.log('ooooo');
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
