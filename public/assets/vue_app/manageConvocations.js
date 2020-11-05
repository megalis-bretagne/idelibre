Vue.filter('formatDateString', function (value) {
    if (value) {
        return dayjs(value).format('DD/MM/YY H:mm')
    }
});

let app = new Vue({
    delimiters: ['${', '}'],
    el: "#app",
    data: {
        convocations: [],
        isAlreadySent: false,
        filter: {
            actor: "",
            guest: "",
            administrative: ""
        }
    },

    computed: {
        filteredConvocations: function () {
            if (!this.filter.actor || this.filter.actor === "") {
                return this.convocations
            }

            let filterLowerCase = this.filter.actor.toLowerCase();

            return this.convocations.filter(convocation =>
                convocation.actor.lastName.toLowerCase().includes(filterLowerCase) ||
                convocation.actor.firstName.toLowerCase().includes(filterLowerCase) ||
                convocation.actor.username.toLowerCase().includes(filterLowerCase)
            )
        }
    },

    methods: {
        sendConvocation(convocationId) {
            axios.post(`/api/convocations/${convocationId}/send`).then(response => {
                updateConvocations(this.convocations, response.data);
                this.isAlreadySent = isAlreadySentSitting(this.convocations);
            });
        },

        sendConvocations() {
            axios.post(`/api/sittings/${getSittingId()}/sendConvocations`).then(() => {
                this.getConvocations();
            });
        },

        getConvocations() {
            axios.get(`/api/convocations/${getSittingId()}`).then(convocations => {
                this.convocations = convocations.data;
                this.isAlreadySent = isAlreadySentSitting(this.convocations);
            })
        },

        resetFilters() {
            this.filter = { actor: "", guest: "", administrative: "" };
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
            app.$set(app.convocations, i, convocation)
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
