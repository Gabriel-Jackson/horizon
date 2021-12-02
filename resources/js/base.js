import moment from 'moment-timezone';

<<<<<<< HEAD
=======
moment.locale('pt-br');
$('.dropdown-toggle').dropdown();
>>>>>>> bc8be47... Customização final do Horizon
export default {
    computed: {
        Horizon() {
            return Horizon;
        },
    },

    methods: {
        /**
         * Format the given date with respect to timezone.
         */
        formatDate(unixTime) {
<<<<<<< HEAD
            return moment(unixTime * 1000).add(new Date().getTimezoneOffset() / 60);
=======
            unixTime = moment(unixTime *1000).isValid()? unixTime * 1000: unixTime;
            return moment(unixTime).add(new Date().getTimezoneOffset() / 60);
>>>>>>> bc8be47... Customização final do Horizon
        },

        /**
         * Format the given date with respect to timezone.
         */
        formatDateIso(date) {
            return moment(date).add(new Date().getTimezoneOffset() / 60);
        },

        /**
         * Extract the job base name.
         */
        jobBaseName(name) {
            if (!name.includes('\\')) return name;

            var parts = name.split('\\');

            return parts[parts.length - 1];
        },

        /**
         * Autoload new entries in listing screens.
         */
        autoLoadNewEntries() {
            if (!this.autoLoadsNewEntries) {
                this.autoLoadsNewEntries = true;
                localStorage.autoLoadsNewEntries = 1;
            } else {
                this.autoLoadsNewEntries = false;
                localStorage.autoLoadsNewEntries = 0;
            }
        },

        /**
         * Convert to human readable timestamp.
         */
        readableTimestamp(timestamp) {
            return this.formatDate(timestamp).format('YYYY-MM-DD HH:mm:ss');
        },
    },
};
