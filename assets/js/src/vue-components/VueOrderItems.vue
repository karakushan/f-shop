<template>
  <div class="fs-order-items">
    <md-table v-model="products" md-card @md-selected="onSelect">
      <md-table-toolbar>
        <div class="md-title">
          <md-icon>shopping_cart</md-icon>
          {{ translations.purchased_items }}
        </div>
        <md-button class="md-raised md-primary" @click="showDialog = true">{{ translations.add_product }}</md-button>
      </md-table-toolbar>
      <md-table-row slot="md-table-row" slot-scope="{ item,index }">
        <md-table-cell md-label="ID">
          {{ item.id }}
          <input type="hidden" :name="'order[_products]['+index+'][ID]'"
                 :value="item.id">
        </md-table-cell>
        <md-table-cell :md-label="translations.photo">
          <md-avatar class="md-large" v-if="item.thumbnail_url">
            <img :src="item.thumbnail_url" :alt="item.title" width="100">
          </md-avatar>
          <md-avatar v-else class="md-large md-avatar-icon"><md-icon>visibility_off</md-icon></md-avatar>

        </md-table-cell>
        <md-table-cell :md-label="translations.name">
          <a :href="item.permalink" target="_blank" v-if="item.id">{{ item.title }}</a>
          <span v-else>{{ item.title }}</span>
          <input type="hidden" :name="'order[_products]['+index+'][name]'" :value="item.title">
        </md-table-cell>
        <md-table-cell :md-label="translations.price">
          {{ itemPrice(item) }}
          <input type="hidden"
                 :name="'order[_products]['+index+'][price]'"
                 :value="item.price">
        </md-table-cell>
        <md-table-cell :md-label="translations.quantity">
          <md-field>
            <md-input style="width: 20px;" type="number" min="1" step="1" size="3"
                      v-model="item.count"
                      :name="'order[_products]['+index+'][count]'"
                      :value="item.count"></md-input>
          </md-field>
        </md-table-cell>
        <md-table-cell :md-label="translations.cost">{{ itemCost(item) }}</md-table-cell>
        <md-table-cell :md-label="translations.action">
          <md-button class="md-fab md-mini md-plain" @click="deleteItem(index)">
            <md-tooltip>{{ translations.delete }}</md-tooltip>
            <md-icon>delete</md-icon>
          </md-button>
        </md-table-cell>
      </md-table-row>

    </md-table>
    <md-toolbar md-elevation="1" class="fs-order-items__footer">
      <md-list>
        <md-list-item md-expand>
          <h4 class="md-list-item-text"> {{ translations.order_price }}: {{ totalAmount }} {{ currency }}
            <input type="hidden" name="order[_amount]" v-model.number="totalAmount">
          </h4>
          <md-list slot="md-expand">
            <md-list-item>
              <h5>{{ translations.cost_goods }}:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number" name="order[_cart_cost]"
                            :value="cartCost"
                            :step=".01"
                            :min="0" disabled="disabled">
                  </md-input>
                  <span class="md-suffix">{{ currency }}</span>
                </md-field>
              </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
              <h5>{{ translations.packaging }}:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number" name="order[_packing_cost]"
                            v-model.number="orderData.packing_cost"
                            :step=".01"
                            :min="0">
                  </md-input>
                  <span class="md-suffix">{{ currency }}</span>
                </md-field>
              </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
              <h5>{{ translations.delivery }}:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number" name="order[_shipping_cost]"
                            v-model.number="orderData.shipping_cost"
                            :step=".01"
                            :min="0">
                  </md-input>
                  <span class="md-suffix">{{ currency }}</span>
                </md-field>
              </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
              <h5>{{ translations.discount }}:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number"
                            name="order[_order_discount]"
                            v-model.number="orderData.discount"
                            :step=".01"
                            :min="0">
                  </md-input>
                  <span class="md-suffix">{{ currency }}</span>
                </md-field>
              </div>
            </md-list-item>
          </md-list>
        </md-list-item>
      </md-list>
    </md-toolbar>

    <md-dialog :md-active.sync="showDialog">
      <md-progress-bar md-mode="indeterminate" v-show="inProcess"></md-progress-bar>
      <md-dialog-title>{{ translations.product_selection }}</md-dialog-title>
      <md-dialog-content>
        <md-field>
          <md-icon>search</md-icon>
          <label>{{ translations.search_input_label }}</label>
          <md-input v-model="search"></md-input>
        </md-field>

        <md-table v-model="searchItems" md-card @md-selected="onSelect" md-card md-fixed-header>
          <md-table-toolbar>
            <div class="md-title">{{ translations.found_products }}: {{ searchItems.length}}</div>
          </md-table-toolbar>

          <md-table-toolbar slot="md-table-alternate-header" slot-scope="{ count }">
            <div class="md-toolbar-section-start">{{ getAlternateLabel(count) }}</div>

            <div class="md-toolbar-section-end">
              <md-button class="md-raised md-primary" @click="addItems()">{{ translations.add }}</md-button>
            </div>
          </md-table-toolbar>

          <md-table-row slot="md-table-row" slot-scope="{ item,index }"
                        md-selectable="multiple">
            <md-table-cell md-label="ID" md-sort-by="id">
              {{ item.id }}
              <input type="hidden" :name="'fs_products['+index+'][ID]'"
                     :value="item.id">
            </md-table-cell>
            <md-table-cell :md-label="translations.photo" md-sort-by="thumbnail_url">
              <md-avatar class="md-large">
                <img :src="item.thumbnail_url" :alt="item.title" width="100" v-if="item.thumbnail_url">
              </md-avatar>

            </md-table-cell>
            <md-table-cell :md-label="translations.name" md-sort-by="name">{{ item.title }}</md-table-cell>
            <md-table-cell :md-label="translations.price" md-sort-by="price">{{ item.price }} {{ item.currency }}</md-table-cell>
            <md-table-cell :md-label="translations.quantity" md-sort-by="count">
              <md-field>
                <md-input style="width: 20px;" type="number" min="1" step="1" size="3"
                          :name="'fs_products['+index+'][count]'"
                          :value="item.count"></md-input>
              </md-field>
            </md-table-cell>
          </md-table-row>
        </md-table>

      </md-dialog-content>

      <md-dialog-actions>
        <md-button class="md-primary" @click="closeDialog()">{{ translations.close }}</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
</template>

<script>
export default {
  name: "VueOrderItems",
  data() {
    return {
      products: this.items,
      selected: [],
      showDialog: false,
      search: '',
      searchItems: [],
      delayTimer: null,
      inProcess: false,
      orderData: this.order,
      translations: window.FS_BACKEND.lang || {},
      currency: window.FS_BACKEND.currency || 'UAH',
    }
  },
  props: {
    items: {
      type: Array,
      default() {
        return []
      }
    },
    order: {
      type: Object,
      default: () => ({})
    },
  },
  methods: {
    itemPrice(item) {
      return Number(item.price).toFixed(2) + ' ' + item.currency
    },
    itemCost(item) {
      let cost = Number(item.price * item.count)
      return cost.toFixed(2) + ' ' + item.currency
    },
    deleteItem(index) {
      this.products.splice(index, 1)
    },
    addItems() {
      this.selected.forEach((item) => {
        this.products.push(item)
      })
      this.selected = []
      this.searchItems = []
      this.search = ''
      this.showDialog = false
    },
    closeDialog() {
      this.selected = []
      this.searchItems = []
      this.search = ''
      this.showDialog = false
    },
    onSelect(items) {
      this.selected = items
    },
    getAlternateLabel(count) {
      let plural = ''

      if (count > 1) {
        plural = 'а'
      }

      return count +' '+this.translations.product+' '+this.translations.selected
    }
  },
  watch: {
    search(newValue, oldValue) {
      clearTimeout(this.delayTimer);
      let comp = this
      this.delayTimer = setTimeout(function () {
        comp.inProcess = true
        jQuery.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {
            action: 'fs_search_product_admin',
            search: newValue
          },
          success: function (data) {
            if (data.success && comp.showDialog) {
              comp.searchItems = typeof data.data !== 'array' ? data.data : []
            } else {
              comp.searchItems = []
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            console.log('error...', xhr);
            //error logging
          },
          complete: function () {
            comp.inProcess = false
          }
        });
      }, 800);
    }
  },
  mounted() {

  },
  computed: {
    cartCost() {
      let cost = 0;
      this.products.forEach((item) => {
        cost += Number(item.price * item.count)
      })
      return Number(cost);
    },
    totalAmount() {
      let amount = 0

      amount += this.cartCost;
      if (Number(this.orderData.packing_cost) > 0) amount += Number(this.orderData.packing_cost)
      if (Number(this.orderData.shipping_cost) > 0) amount += Number(this.orderData.shipping_cost)
      if (Number(this.orderData.discount) > 0) amount -= Number(this.orderData.discount)

      return Number(amount).toFixed(2);
    }
  },
}
</script>

<style lang="scss" scoped>
.fs-order-items {
  margin-bottom: 30px;

  &__footer {
    padding: 0;

    .md-list {
      background: #f5f5f5;
      width: 100%;
      padding: 0;
    }

    .md-list-item {
      margin-bottom: 0;
    }
  }

}

.md-dialog-content {
  min-width: 540px;
}
</style>