Vue.filter('formatDateString', function (value) {
    if (value) {
        return dayjs(value).format('DD/MM/YY H:mm')
    }
});

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        actorConvocations: [],
        guestConvocations: [],
        employeeConvocations: [],
        isAlreadySentActors: false,
        isAlreadySentGuests: false,
        isAlreadySentEmployees: false,
        filter: {
            actor: "",
            guest: "",
            employee: ""
        }
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
            if(type === 'Actor') {
                url += "?userProfile=Actor"
            }
            if(type === 'Employee') {
                url += "?userProfile=Employee"
            }

            if(type === 'Guest') {
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
            })
        },

        resetFilters() {
            this.filter = { actor: "", guest: "", employees: "" };
        }

    },


    mounted() {
        this.getConvocations();
    }
});

function getSittingId() {
    return window.location.pathname.split('/')[3];
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
