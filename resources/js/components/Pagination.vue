<script>
 export default {
        props: [
          'page',
          'totalPages',
        ],
        /**
         * The component's data.
         */
        data() {
          return {}
        },

        methods: {
          setPage(page){
            this.$emit('changePage',page);
          },
          range(start, end){
            return _.range(start,end);
          }
        }

 }
</script>

<template>
  <div class="p-3 d-flex justify-content-between border-top">
    <button @click="setPage(page-1)" class="btn btn-secondary btn-md" :disabled="page==1">Anterior</button>
    <ul class="pagination justify-content-center">
         <li v-if="page > 1" class="page-item  mr-1">
            <button @click="setPage(1)" class="btn btn-secondary btn-md text-bold"> 
                Primeira Página
            </button>
        </li>
        <li v-if="page > 6" class="page-item mr-1">
            <button @click="setPage(page-6)" class="btn btn-secondary btn-md"> 
                ...
            </button>
        </li>

        <li v-for="pg in range(page-5,page+5)" v-if="pg > 0 && pg <= totalPages" 
            class="page-item" :class="pg < page+5? 'mr-1':''">
            <a @click.prevent="setPage(pg)" class="btn btn-secondary btn-md" 
                :class="page==pg? 'active': ''" href="#">
                {{pg}}
            </a>
        </li>

        <li v-if="page < totalPages-5" class="page-item mr-1">
            <button @click="setPage(page+5)" class="btn btn-secondary btn-md text-bold"> 
                ...
            </button>
        </li>

        <li v-if="page < totalPages" class="page-item">
            <button @click="setPage(totalPages)" class="btn btn-secondary btn-md text-bold"> 
                Última Página
            </button>
        </li>
    </ul>
    <button @click="setPage(page+1)" class="btn btn-secondary btn-md" :disabled="page>=totalPages">Próxima</button>
  </div>
</template>