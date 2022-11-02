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
            otherdocs: [],
            messageInfo: null,
            showModal: false,
            uploadPercent: 0,
            messageError: null,

        },

        methods: {

            otherdocChange($event) {
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
                        id: null
                    };
                    this.otherdocs.push(otherdoc);
                }
                isDirty = true;
            },

            removeOtherdoc(index) {
                this.otherdocs.splice(index, 1);
                isDirty = true;
            },

            save() {
                let formData = new FormData();
                addOtherdocFiles(this.otherdocs, formData);
                setOtherdocsRank(this.otherdocs);
                formData.append('otherdocs', JSON.stringify(this.otherdocs));
                this.showModal = true;
                this.uploadPercent = 0;
                const config = {
                    onUploadProgress: (progressEvent) => {
                        this.uploadPercent = Math.round((progressEvent.loaded * 100) / progressEvent.total);

                    }
                }

                axios.post(`/api/otherdocs/${getSittingId()}`,
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
                    console.log(e.response.data);
                    let errorBody = e.response.data;
                    this.showMessageError(errorBody.message ? errorBody.message :'Impossible d\'enregistrer les modifications');
                });
            },

            move(fromIndex, toIndex) {
                arrayMove(this.otherdocs, fromIndex, toIndex);
            },


            cancel() {
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
                this.messageError = msg;
                setTimeout(() => this.messageError = null, 3000);
            },


        },
        mounted() {
            Promise.all([
                axios.get(`/api/otherdocs/${getSittingId()}`)
            ]).then((response) => {
                this.otherdocs = response[0].data;
            });
        }
    })
;

function addOtherdocFiles(otherdocs, formData) {
    for (let i = 0; i < otherdocs.length; i++) {
        if (isNewOtherdoc(otherdocs[i])) {
            formData.append(`autre_${i}_rapport`, otherdocs[i].file, otherdocs[i].file.name);
            otherdocs[i].linkedFileKey = `autre_${i}_rapport`;
        }
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

function getSittingId() {
    return window.location.pathname.split('/')[3];
}

function isNewOtherdoc(otherdoc) {
    return !otherdoc.id;
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

document.addEventListener('DOMContentLoaded', function() {
    window.onscroll = function(ev) {
        document.getElementById("cRetour").className = (window.pageYOffset > 100) ? "cVisible" : "cInvisible";
    };
    $('#cRetour').click( function() {
        $('html,body').animate({scrollTop: 0}, 'slow');
    });
});