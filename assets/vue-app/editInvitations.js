import './vue-app.css';

import Vue from 'vue/dist/vue';
import axios from 'axios';
import VueSelect from 'vue-select';
import $ from 'jquery';
import 'bootstrap';


Vue.component('v-select', VueSelect);

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        inSittingActors: [],
        inSittingEmployees: [],
        inSittingGuests: [],
        notInSittingActors: [],
        notInSittingEmployees: [],
        notInSittingGuests: [],
        alreadySentConvocationActorIds: [],
        alreadySentConvocationEmployeesIds: [],
        alreadySentConvocationGuestsIds: [],
        removedUsers: [],
        addedActors: [],
        addedEmployees: [],
        addedGuests: [],
        messageInfo: null
    },

    methods: {

        removeActor(userId) {
            //TODO Faire le remove our tous les type a la suite en ignorant si le champ est absent
            removeInSittingUser(this.inSittingActors, userId);
            removeInSittingUser(this.inSittingGuests, userId);
            removeInSittingUser(this.inSittingEmployees, userId);
            this.removedUsers.push(userId);
            this.updateSelectDataAfterRemove(userId);
        },

        updateSelectDataAfterRemove(userId) {
            Promise.all([
                axios.get(`/api/users/${userId}`),
            ]).then((response) => {
                this.notInSittingActors.push(response[0].data['user']);
                axios.put(`/api/users/sittings/${getSittingId()}`, {
                    removedUsers: this.removedUsers,
                    addedActors: this.addedActors,
                    addedEmployees: this.addedEmployees,
                    addedGuests: this.addedGuests
                }).then((response) => {
                    this.removedUsers = [];
                    this.addedActors = [];
                    this.addedEmployees = [];
                    this.addedGuests = [];
                    this.getUsers();
                })
            });
        },

        save() {
            axios.put(`/api/users/sittings/${getSittingId()}`, {
                removedUsers: this.removedUsers,
                addedActors: this.addedActors,
                addedEmployees: this.addedEmployees,
                addedGuests: this.addedGuests
            }).then((response) => {
                this.removedUsers = [];
                this.addedActors = [];
                this.addedEmployees = [];
                this.addedGuests = [];
                this.getUsers();
                window.scrollTo(0, 0);
                this.showMessage('Modifications enregistrées');
            })
        },

        cancel() {
            this.removedUsers = [];
            this.addedActors = [];
            this.addedEmployees = [];
            this.addedGuests = [];
            this.getUsers();
            window.scrollTo(0, 0);
            this.showMessage('Modifications annulées');
        },

        showMessage(msg) {
            this.messageInfo = msg;
            setTimeout(() => this.messageInfo = null, 3000);
        },

        getUsers() {
            Promise.all([
                axios.get(`/api/users/sittings/${getSittingId()}`),
                axios.get(`/api/users/sittings/${getSittingId()}/not`),
                axios.get(`/api/users/sittings/${getSittingId()}/sent`),
            ]).then((response) => {
                this.inSittingActors = response[0].data['actors'];
                this.inSittingEmployees = response[0].data['employees'];
                this.inSittingGuests = response[0].data['guests'];

                this.notInSittingActors = response[1].data['actors'];
                this.notInSittingEmployees = response[1].data['employees'];
                this.notInSittingGuests = response[1].data['guests'];

                this.alreadySentConvocationActorIds = response[2].data['actors'];
                this.alreadySentConvocationEmployeesIds = response[2].data['employees'];
                this.alreadySentConvocationGuestsIds = response[2].data['guests'];
            });
        },

        alreadySentConvocation(userId) {
            return (this.alreadySentConvocationActorIds.indexOf(userId) > -1) ||
                (this.alreadySentConvocationGuestsIds.indexOf(userId) > -1) ||
                (this.alreadySentConvocationEmployeesIds.indexOf(userId) > -1);
        }
    },

    mounted() {
        this.getUsers();
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

function removeInSittingUser(actors, actorId) {
    const index = findActorIndex(actors, actorId);
    if (index === -1) {
        return;
    }
    app.$delete(actors, index)
}


function isDirty() {
    return (app.removedUsers.length || app.addedActors.length || app.addedEmployees.length || app.addedGuests.length);
}


// OH que ce n'est pas beau du jquery dans une app vue
// TODO Tout faire en vue

$('.change-tab').click(function (event) {

    if ($(this).hasClass('active')) {
        event.preventDefault();
        return false;
    }

    if (isDirty()) {
        event.preventDefault();
        $('#confirm-btn').attr('href', $(this).attr('href'));
        $('#confirm-not-save').modal();

        return false;
    }
    return true;
});
