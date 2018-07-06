<template>
  <div>
    <datatable>
      <thead>
        <tr>
          <th>Ting</th>
          <th>Strekkode</th>
          <th>Merknader</th>
          <th>Utlån</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data">
          <td>
            {{ item.thing }}
          </td>
          <td>
            <a :href="'/items/' + item.id">{{ item.dokid }}</a>
          </td>
          <td>
            {{ item.note }}
          </td>
          <td>
            <span v-if="item.loan" class="badge badge-success">Utlånt</span>
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
      }
  }
</script>