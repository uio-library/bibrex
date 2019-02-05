<template>
  <div>
    <datatable>
      <thead>
        <tr>
          <th>Strekkode</th>
          <th>Eksemplarinfo</th>
          <th v-if="showLibrary">Bibliotek</th>
          <th v-if="showThing">Ting</th>
          <th>Registrert</th>
          <th>Sist utlånt</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data">
          <td>
            <a :href="'/items/' + item.id">{{ item.barcode }}</a>
          </td>
          <td>
            {{ item.note }}
          </td>
          <td v-if="showLibrary">
            {{ item.library.name }}
          </td>
          <td v-if="showThing">
            {{ item.thing.name }}
          </td>
          <td>
            {{ item.created_at.split(' ')[0] }}
          </td>
          <td>
            <span v-if="item.last_loan && !item.last_loan.deleted_at">
              Utlånt nå
            </span>
            <span v-else-if="item.last_loan">
              {{ item.last_loan.created_at.split(' ')[0] }}
            </span>
            <span v-else>
              Aldri
            </span>
          </td>
        </tr>
      </tbody>
    </datatable>
  </div>
</template>

<script>
  export default {
      props: {
        data: Array,
        showLibrary: Boolean,
        showThing: Boolean,
      }
  }
</script>