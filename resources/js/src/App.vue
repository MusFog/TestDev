<script setup>
import { ref } from 'vue'
import axios from 'axios'

const form = ref({
    name: '',
    website: '',
    phone: '',
    deal: '',
    stage: 'Qualification',
})

const submitting = ref(false)
const message = ref('')
const table = ref('')
const stages = ref([
    'Qualification',
    'Needs Analysis',
    'Value Proposition',
    'Proposal/Price Quote',
    'Negotiation/Review',
    'Closed Won',
    'Closed Lost'
])

async function submit() {
    submitting.value = true
    message.value = ''
    table.value  = ''

    try {
        const dataLoad = {
            account_name: form.value.name,
            website: form.value.website,
            phone: form.value.phone,
            deal_name: form.value.deal,
            stage: form.value.stage,
        }

        const { data: resp } = await axios.post('api/zoho/account', dataLoad)
        message.value = resp.data.message
        table.value  = resp.data
    } catch (e) {
        message.value =
            e.response?.data?.data?.message ||
            e.response?.data?.message ||
            'Помилка створення'
        table.value = e.response?.data?.data || 'error'
    } finally {
        submitting.value = false
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="form-container">
        <h2>Створити Account і Deal</h2>

        <div class="field">
            <label for="account_name">Account Name</label>
            <input id="account_name" v-model="form.name" required placeholder="Account Name" />
        </div>

        <div class="field">
            <label for="website">Website</label>
            <input id="website" v-model="form.website" type="url" required placeholder="Website" />
        </div>

        <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" v-model="form.phone" type="tel" required placeholder="Phone" />
        </div>

        <div class="field">
            <label for="deal_name">Deal Name</label>
            <input id="deal_name" v-model="form.deal" required placeholder="Deal Name" />
        </div>

        <div class="field">
            <label for="stage">Stage</label>
            <select id="stage" v-model="form.stage" required>

          <option v-for="s in stages" :key="s" :value="s">{{ s }}</option>
            </select>
        </div>

        <button type="submit" :disabled="submitting">
            {{ submitting ? 'Saving…' : 'Create All' }}
        </button>

        <div v-if="message" class="mt-4">
            <strong>{{ message }}</strong>
        </div>
        <div v-if="table" class="mt-4">
            <strong>{{ table }}</strong>
        </div>
    </form>
</template>

<style scoped>
.form-container {
    max-width: 400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.field label {
    display: block;
    margin-bottom: 0.25rem;
}
.field input,
.field select {
    width: 100%;
    padding: 0.5rem;
    box-sizing: border-box;
}
.mt-4 { margin-top: 1rem; }
</style>
