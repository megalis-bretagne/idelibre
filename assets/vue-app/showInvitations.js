import './vue-app.css';
import Vue from 'vue/dist/vue';
import axios from 'axios';


Vue.filter('formatDateString', function (value, timezone) {
    if (value && timezone) {
        const date = new Date(value);
        const tz = date.toLocaleString("utc", {timeZone: timezone});
        return tz.substring(0, tz.length - 3)
    }
});

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        comelusId: null,
        isComelus: false,
        isComelusSending: false,
        lsvoteId: null,
        actorConvocations: [],
        guestConvocations: [],
        employeeConvocations: [],
        isAlreadySentActors: false,
        isAlreadySentGuests: false,
        isAlreadySentEmployees: false,
        isArchived: true,
        showModalComelus: false,
        previewUrl: "",
        previewData: "",
        previewSubject: "",
        showModalMailExample: false,
        filter: {
            actor: "",
            guest: "",
            employee: ""
        },
        showModalAttendance: false,
        attendanceStatus: [],
        changedAttendance: [],
        errorMessage: null,
        infoMessage: null,
        showModalNotifyAgain: false,
        notification: {
            object: "",
            content: "",
        },
        convocationIdCurrent: "",
        isInvitation: false,
        timezone: "",
        options: "",
        actorsInSitting : [],
    },

    computed: {
        filteredActorConvocations: function () {
            return filter(this.actorConvocations, this.filter.actor);
        },

        filteredEmployeeConvocations: function () {
            return filter(this.employeeConvocations, this.filter.employee);
        },

        filteredGuestConvocations: function () {
            return filter(this.guestConvocations, this.filter.guest);
        }
    },

    methods: {
        sendConvocation(convocationId) {

            axios.post(`/api/convocations/${convocationId}/send`).then(response => {
                updateConvocations(this.actorConvocations, response.data);
                updateConvocations(this.guestConvocations, response.data);
                updateConvocations(this.employeeConvocations, response.data);

                this.isAlreadySentActors = isAlreadySentSitting(this.actorConvocations);
                this.isAlreadySentGuests = isAlreadySentSitting(this.guestConvocations);
                this.isAlreadySentEmployees = isAlreadySentSitting(this.employeeConvocations);
            });
        },

        sendConvocations(type) {
            let url = `/api/sittings/${getSittingId()}/sendConvocations`;
            if (type === 'Actor') {
                url += "?userProfile=Actor"
            }
            if (type === 'Employee') {
                url += "?userProfile=Employee"
            }

            if (type === 'Guest') {
                url += "?userProfile=Guest"
            }

            axios.post(url).then(() => {
                this.getConvocations();
            });
        },

        getConvocations() {
            axios.get(`/api/convocations/${getSittingId()}`).then(convocations => {
                this.actorConvocations = convocations.data['actors'];
                this.guestConvocations = convocations.data['guests'];
                this.employeeConvocations = convocations.data['employees'];

                this.isAlreadySentActors = isAlreadySentSitting(this.actorConvocations);
                this.isAlreadySentGuests = isAlreadySentSitting(this.guestConvocations);
                this.isAlreadySentEmployees = isAlreadySentSitting(this.employeeConvocations);
                this.convocationIdCurrent = getConvocationCurrentId(convocations);
            })
        },

        getSitting() {
            axios.get(`/api/sittings/${getSittingId()}`).then(response => {
                const sitting = response.data;
                this.comelusId = sitting?.comelusId ?? null;
                this.isComelus = sitting?.type.isComelus ?? false;
                this.isArchived = sitting.isArchived;
                this.lsvoteId = sitting?.lsvoteSitting?.lsvoteSittingId;
            })
        },

        getSittingTimezone() {
            axios.get(`/api/sittings/${getSittingId()}/timezone`)
                .then(response => {
                    this.timezone = response.data.timezone
                });
        },


        resetFilters() {
            this.filter = {actor: "", guest: "", employees: ""};
        },

        openShowModalNotifyAgain() {
            this.notification.object = ""
            this.notification.content = ""
            this.showModalNotifyAgain = true;
        },

        sendNotifyAgain() {
            axios.post(`/api/sittings/${getSittingId()}/notifyAgain`, this.notification).then(
                (response) => {
                    this.setInfoMessage("Messages envoyés");

                })
                .catch((e) => {
                    console.log(e);
                    this.setErrorMessage("Erreur lors de l'envoi");

                })
                .finally(() => {
                    this.showModalNotifyAgain = false
                });
        },

        sendComelus() {
            this.isComelusSending = true;
            this.showModalComelus = true;
            axios.post(`/api/sittings/${getSittingId()}/sendComelus`, this.notification).then(
                (response) => {
                    this.setInfoMessage("Documents envoyés via comelus");
                    this.comelusId = response.data['comelusId']
                })
                .catch((e) => {
                    console.log(e);
                    this.setErrorMessage("Erreur lors de l'envoi");

                }).finally(() => {
                this.isComelusSending = false;
                this.showModalComelus = false;
            });
        },
        sendLsvote() {
            axios.post(`/api/sittings/${getSittingId()}/sendLsvote`)
                .then((response) => {
                    this.setInfoMessage("Séance envoyée à lsvote");
                    this.lsvoteId = response.data['lsvoteId']
                })
                .catch((e) => {
                    console.log(e);
                    this.setErrorMessage("Erreur lors de l'envoi");
                });
        },

        openShowModalAttendance() {
            this.attendanceStatus = [
                ...formatAttendanceStatus(this.actorConvocations),
                ...formatAttendanceStatus(this.employeeConvocations),
                ...formatAttendanceStatus(this.guestConvocations)
            ];

            this.showModalAttendance = true;
        },

        changeAttendance(status) {
            this.changedAttendance.push({
                convocationId: status.convocationId,
                attendance: status.attendance,
                replacement: status.replacement,
                deputy: status.deputy
            })
            console.log(status)
        },

        saveAttendance() {
            axios.post(`/api/convocations/attendance`, this.changedAttendance).then(
                (response) => {
                    this.getConvocations()
                })
                .catch((e) => {
                    console.log("erreur : " + e);
                    this.setErrorMessage("Erreur lors de l'enregistrement des présences")

                })
                .finally(() => {
                    this.showModalAttendance = false
                    this.changedAttendance = [];
                });
        },

        setErrorMessage(msg) {
            this.errorMessage = msg
            setTimeout(() => this.errorMessage = null, 3000);
        },
        setInfoMessage(msg) {
            this.infoMessage = msg
            setTimeout(() => this.infoMessage = null, 3000);
        },

        openShowModalMailExample(convocationId, invitation) {
            this.convocationId = convocationId;
            this.previewUrl = `/api/convocations/previewForSecretary/${convocationId}`;
            this.isInvitation = false;
            if (invitation) {
                this.previewUrl = `/api/convocations/previewForSecretaryOther/${convocationId}`;
                this.isInvitation = true;
            }
            this.showModalMailExample = true;
        },
    },


    mounted() {
        this.getSittingTimezone();
        this.getConvocations();
        this.getSitting();
        this.saveAttendance();
        this.hideAndSeek(this.status)
    }
});


function formatAttendanceStatus(convocations) {
    let status = []
    for (let i = 0; i < convocations.length; i++) {
        let convocation = convocations[i];
        status.push({
            convocationId: convocation.id,
            firstName: convocation.user.firstName,
            lastName: convocation.user.lastName,
            attendance: convocation.attendance,
            replacement: convocation.replacement,
            deputy: convocation.deputy,
            category: convocation.category,
        })
    }
    return status;
}


function getSittingId() {
    return window.location.pathname.split('/')[3];
}

function getActorId() {
    return window.location.pathname.split('/')[4];
}

function getConvocationCurrentId(convocations) {
    let convocationCurrentId = formatAttendanceStatus(convocations.data['actors'])[0].convocationId;
    return convocationCurrentId;
}

function updateConvocations(convocations, convocation) {
    for (let i = 0; i < convocations.length; i++) {
        if (convocations[i].id === convocation.id) {
            app.$set(convocations, i, convocation)
        }
    }
}

function isAlreadySentSitting(convocations) {
    for (let i = 0; i < convocations.length; i++) {
        if (!convocations[i].sentTimestamp) {
            return false;
        }
    }
    return true;
}


function filter(convocations, search) {

    if (!search || search === "") {
        return convocations
    }

    let filterLowerCase = search.toLowerCase();
    return convocations.filter(convocation =>
        convocation.user.lastName.toLowerCase().includes(filterLowerCase) ||
        convocation.user.firstName.toLowerCase().includes(filterLowerCase) ||
        convocation.user.username.toLowerCase().includes(filterLowerCase)
    )

}


