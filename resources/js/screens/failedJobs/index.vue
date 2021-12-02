<script >
    import Pagination from '../../components/Pagination.vue';
    export default {
        components: {
            Pagination,
        },
        /**
         * The component's data.
         */
        data() {
            return {
                tagSearchPhrase: '',
                searchTimeout: null,
                ready: false,
                loadingNewEntries: false,
                hasNewEntries: false,
                page: 1,
                perPage: 10,
                totalPages: 1,
                jobs: [],
                retryingJobs: [],
            };
        },

        /**
         * Prepare the component.
         */
        mounted() {
            document.title = "Api Produtos - Jobs com Falha";

            this.loadJobs();

            this.refreshJobsPeriodically();
        },

        /**
         * Clean after the component is destroyed.
         */
        destroyed() {
            clearInterval(this.interval);
        },


        /**
         * Watch these properties for changes.
         */
        watch: {
            '$route'() {
                this.page = 1;

                this.loadJobs();
            },

            tagSearchPhrase() {
                clearTimeout(this.searchTimeout);
                clearInterval(this.interval);

                this.searchTimeout = setTimeout(() => {
                    this.loadJobs();
                    this.refreshJobsPeriodically();
                }, 500);
            }
        },


        methods: {
            /**
             * Load the jobs of the given tag.
             */
            loadJobs(starting = 0, refreshing = false) {
                if (!refreshing) {
                    this.ready = false;
                }

                var tagQuery = this.tagSearchPhrase ? 'tag=' + this.tagSearchPhrase + '&' : '';

                this.$http.get(Horizon.basePath + '/api/jobs/failed?' + tagQuery + 'starting_at=' + starting)
                    .then(response => {
                        if (!this.$root.autoLoadsNewEntries && refreshing && !response.data.jobs.length) {
                            return;
                        }


                        if (!this.$root.autoLoadsNewEntries && refreshing && this.jobs.length && _.first(response.data.jobs).id !== _.first(this.jobs).id) {
                            this.hasNewEntries = true;
                        } else {
                            this.jobs = response.data.jobs;

                            this.totalPages = Math.ceil(response.data.total / this.perPage);
                        }

                        this.ready = true;
                    });
            },


            loadNewEntries() {
                this.jobs = [];

                this.loadJobs(0, false);

                this.hasNewEntries = false;
            },


            /**
             * Retry the given failed job.
             */
            retry(id) {
                if (this.isRetrying(id)) {
                    return;
                }

                this.retryingJobs.push(id);

                this.$http.post(Horizon.basePath + '/api/jobs/retry/' + id)
                    .then((response) => {
                        setTimeout(() => {
                            this.retryingJobs = _.reject(this.retryingJobs, job => job == id);
                        }, 5000);
                    }).catch(error => {
                        this.retryingJobs = _.reject(this.retryingJobs, job => job == id);
                    });
            },


            /**
             * Determine if the given job is currently retrying.
             */
            isRetrying(id) {
                return _.includes(this.retryingJobs, id);
            },


            /**
             * Determine if the given job has completed.
             */
            hasCompleted(job) {
                return _.find(job.retried_by, retry => retry.status == 'completed');
            },


            /**
             * Determine if the given job was retried.
             */
            wasRetried(job) {
                return job.retried_by && job.retried_by.length;
            },


            /**
             * Determine if the given job is a retry.
             */
            isRetry(job) {
                return job.payload.retry_of;
            },


            /**
             * Refresh the jobs every period of time.
             */
            refreshJobsPeriodically() {
                this.interval = setInterval(() => {
                    this.loadJobs((this.page - 1) * this.perPage, true);
                }, 3000);
            },
            /**
             * Change the page
             */
            setPage(page) {
                console.log(page);
                this.loadJobs(
                    (page - 1) * this.perPage
                );

                this.page = page;

                this.hasNewEntries = false;
            },
        }
    }
</script>

<template>
    <div>
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Jobs com Falha</h3>
                <div class="card-tools">
                    <input type="text" class="form-control" style="width:200px"
                        v-model="tagSearchPhrase" placeholder="Pesquisar Tags">
                </div>
            </div>
            <div class="card-body">
                <div v-if="!ready" class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
                        <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
                    </svg>

                    <span>Carregando...</span>
                </div>


                <div v-if="ready && jobs.length == 0" class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
                    <span>Não existe nenhum Job com falha</span>
                </div>

                <table v-if="ready && jobs.length > 0" class="table table-hover table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Job</th>
                        <th class="text-right">Tempo de Execução</th>
                        <th class="text-right">Falhou em</th>
                        <th class="text-right">Tentar Novamente</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-if="hasNewEntries" key="newEntries" class="dontanimate">
                        <td colspan="100" class="text-center card-bg-secondary py-1">
                            <small><a href="#" v-on:click.prevent="loadNewEntries" v-if="!loadingNewEntries">Carregar Novas Entradas</a></small>

                            <small v-if="loadingNewEntries">Carregando...</small>
                        </td>
                    </tr>

                    <tr v-for="job in jobs" :key="job.id">
                        <td>
                            <router-link :title="job.name" :to="{ name: 'failed-jobs-preview', params: { jobId: job.id }}">
                                {{ jobBaseName(job.name) }}
                            </router-link>

                            <small class="badge badge-secondary badge-sm"
                                v-tooltip:top="`Total de tentativas: ${job.retried_by.length}`"
                                v-if="wasRetried(job)">
                                Repetido
                            </small>

                            <br>

                            <small class="text-muted">
                                Fila: {{job.queue}}
                                | Tentativas: {{ job.payload.attempts }}
                                <span v-if="isRetry(job)">
                                | Nova tentativa de
                                <router-link :title="job.name" :to="{ name: 'failed-jobs-preview', params: { jobId: job.payload.retry_of }}">
                                    {{ job.payload.retry_of.split('-')[0] }}
                                </router-link>
                                </span>
                                <span v-if="job.payload.tags && job.payload.tags.length" class="text-break">
                                | Tags: {{ job.payload.tags && job.payload.tags.length ? job.payload.tags.join(', ') : '' }}
                                </span>
                            </small>
                        </td>

                        <td class="text-right">
                            <span>{{ job.failed_at ? String((job.failed_at - job.reserved_at).toFixed(2))+'s' : '-' }}</span>
                        </td>

                        <td class="text-right">
                            {{ readableTimestamp(job.failed_at) }}
                        </td>

                        <td class="text-right ">
                            <a href="#" @click.prevent="retry(job.id)" v-if="!hasCompleted(job)">
                                <svg class="fill-primary" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;" :class="{spin: isRetrying(job.id)}">
                                    <path d="M10 3v2a5 5 0 0 0-3.54 8.54l-1.41 1.41A7 7 0 0 1 10 3zm4.95 2.05A7 7 0 0 1 10 17v-2a5 5 0 0 0 3.54-8.54l1.41-1.41zM10 20l-4-4 4-4v8zm0-12V0l4 4-4 4z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer">
                <Pagination v-if="ready && jobs.length" 
                    :page='page' 
                    :totalPages='totalPages' 
                    @changePage='setPage($event)'
                />
            </div>
        </div>

    </div>
</template>
