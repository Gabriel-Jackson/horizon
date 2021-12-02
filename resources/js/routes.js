export default [
    { path: '/', redirect: '/dashboard' },

    {
        path: '/dashboard',
        name: 'dashboard',
        component: require('./screens/dashboard').default,
    },

    {
        path: '/queues',
        name: 'queues',
        component: require('./screens/queues/index').default,
    },

    {
        path: '/jobs/:type',
        name: 'jobs',
        component: require('./screens/recentJobs/index').default,
    },

    {
        path: '/jobs/pending/:jobId',
        name: 'pending-jobs-preview',
        component: require('./screens/recentJobs/job').default,
    },

    {
        path: '/jobs/completed/:jobId',
        name: 'completed-jobs-preview',
        component: require('./screens/recentJobs/job').default,
    },

    {
        path: '/failed',
        name: 'failed-jobs',
        component: require('./screens/failedJobs/index').default,
    },

    {
        path: '/failed/:jobId',
        name: 'failed-jobs-preview',
        component: require('./screens/failedJobs/job').default,
    },

    {
        path: '/batches',
        name: 'batches',
        component: require('./screens/batches/index').default,
    },

    {
        path: '/batches/:batchId',
        name: 'batches-preview',
        component: require('./screens/batches/preview').default,
    },
];
