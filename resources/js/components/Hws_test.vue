<template>
    <div>
        <h2>Hardware</h2>
        <div class="card card-body mb-2" v-for="hw in hws" v-bind:key="hw.id">
            <h3>{{ hw.id }}</h3>
            <p>{{ hw.name }}</p>
            <hr>
            <button @click="editHw(hw)" class="btn btn-warning mb-2">Edit</button>
            <button @click="deleteHw(hw.id)" class="btn btn-danger">Delete</button>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                hws: [],
                hw: {
                    id: '',
                    name: '',
                }, 
                hw_id: '',
                pagination: {},
                edit: false
            }
        },

        created() {
            this.fetchHws();
        },

        methods: { 
            fetchHws(page_url) {
                let vm = this;
                page_url = page_url || '/api/hws';
                fetch(page_url)
                    .then(res => res.json())
                    .then(res => {
                        this.hws = res.data
                        vm.makePagination(res.meta, res.links);
                    })
                    .catch(err => console.log(err));
            },
            makePagination(meta, links) {
                let pagination = {
                    current_page: meta.current_page,
                    last_page: meta.last_page,
                    next_page_url: links.next,
                    prev_page_url: links.prev
                };

                this.pagination = pagination;
            },
            deleteHw(id) {
                if(confirm('Are You Sure?')) {
                    fetch(`api/hw/${id}` , {
                        method: 'delete'
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert('Hardware Removed');
                        this.fetchHws();
                    })
                }
            },
            addHw() {
                if(this.edit === false) {
                    // Add
                    fetch('api/hw' , {
                        method: 'post',
                        body: JSON.stringify(this.hw),
                        headers: {
                            'content-type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.hw.id = '';
                        this.hw.name = '';
                        alert('Hardware Added');
                        this.fetchHws();
                    })
                } else {
                    // Update
                    fetch('api/hw' , {
                        method: 'put',
                        body: JSON.stringify(this.hw),
                        headers: {
                            'content-type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.hw.id = '';
                        this.hw.name = '';
                        alert('Hardware Updated');
                        this.fetchHws();
                    })
                }
            },
            editHw(hw) {
                this.edit = true;
                this.hw.id = hw.id;
                this.hw.hw_id = hw.id;
                this.hw.id = hw.id;
                this.hw.name = hw.name;
            }
        }
    }
</script>
