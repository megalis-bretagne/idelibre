Vue.component('v-select', VueSelect.VueSelect);

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        inSittingActors: [],
        notInSittingActors: [],
        alreadySentConvocationActorIds : [],
        removedActors: [],
        addedActors: [],
        messageInfo: null
    },

    methods: {

        removeActor(actorId) {
            removeInSittingActor(this.inSittingActors, actorId);
            this.removedActors.push(actorId);
        },

        save() {
            axios.put(`/api/actors/sittings/${getSittingId()}`, {
                removedActors: this.removedActors,
                addedActors: this.addedActors
            }).then((response) => {
                this.removedActors = [];
                this.addedActors = [];
                this.getActors();
                this.showMessage('Modifications enregistrées');
            })
        },

        cancel() {
            this.removedActors = [];
            this.addedActors = [];
            this.getActors();
            this.showMessage('Modifications annulées');
        },

        showMessage(msg) {
          this.messageInfo = msg;
          setTimeout(() => this.messageInfo = null, 3000);
        },

        getActors() {
            Promise.all([
                axios.get(`/api/actors/sittings/${getSittingId()}`),
                axios.get(`/api/actors/sittings/${getSittingId()}/not`),
                axios.get(`/api/actors/sittings/${getSittingId()}/sent`),
            ]).then((response) => {
                this.inSittingActors = response[0].data;
                this.notInSittingActors = response[1].data;
                this.alreadySentConvocationActorIds = response[2].data;
            });
        },

        alreadySentConvocation(actorId) {
            return this.alreadySentConvocationActorIds.indexOf(actorId) > -1;
        }
    },

    mounted() {
        this.getActors();
    }
});

function getSittingId() {
    return window.location.pathname.split('/')[3];
}

function findActorIndex(actors, actorId) {
    for (let i = 0; i < actors.length; i++) {
        if (actors[i].id === actorId) {
            return i;
        }
    }
    return -1;
}

function removeInSittingActor(actors, actorId) {
    const index = findActorIndex(actors, actorId);
    if (index === -1) {
        return;
    }
    app.$delete(app.inSittingActors, index)
}


function isDirty() {
    return (app.removedActors.length || app.addedActors.length);
}
