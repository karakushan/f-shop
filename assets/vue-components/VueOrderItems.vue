<template>
  <div class="fs-order-items">
    <md-table v-model="products" md-card @md-selected="onSelect">
      <md-table-toolbar>
        <h1 class="md-title">
          <md-icon>shopping_cart</md-icon>
          Купленные товары
        </h1>
        <md-button class="md-raised md-primary" @click="showDialog = true">Добавить товар</md-button>
      </md-table-toolbar>
      <md-table-row slot="md-table-row" slot-scope="{ item,index }">
        <md-table-cell md-label="ID">
          {{ item.id }}
          <input type="hidden" :name="'fs_products['+index+'][ID]'"
                 :value="item.id">
        </md-table-cell>
        <md-table-cell md-label="Фото">
          <md-avatar class="md-large">
            <img :src="item.thumbnail_url" :alt="item.title" width="100" v-if="item.thumbnail_url">
          </md-avatar>

        </md-table-cell>
        <md-table-cell md-label="Название">
          <a :href="item.permalink" target="_blank">{{ item.title }}</a>
        </md-table-cell>
        <md-table-cell md-label="Цена">{{ item.price }} {{ item.currency }}</md-table-cell>
        <md-table-cell md-label="К-во">
          <md-field>
            <md-input style="width: 20px;" type="number" min="1" step="1" size="3"
                      :name="'fs_products['+index+'][count]'"
                      :value="item.count"></md-input>
          </md-field>
        </md-table-cell>
        <md-table-cell md-label="Стоимость">{{ item.cost }} {{ item.currency }}</md-table-cell>
        <md-table-cell md-label="Действие">
          <md-button class="md-fab md-mini md-plain" @click="deleteItem(index)">
            <md-tooltip>Удалить</md-tooltip>
            <md-icon>delete</md-icon>
          </md-button>
        </md-table-cell>
      </md-table-row>

    </md-table>
    <md-toolbar md-elevation="1" class="fs-order-items__footer">
      <md-list>
        <md-list-item md-expand>
          <h4 class="md-list-item-text"> Стоимость товаров: {{ totalAmount }} UAH</h4>
          <md-list slot="md-expand">
            <md-list-item>
              <h5>Стоимость товаров:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number" name="order[_cart_cost]"
                            v-model.number="orderData.cart_cost"
                            :step=".01"
                            :min="0" disabled="disabled">
                  </md-input>
                  <span class="md-suffix">UAH</span>
                </md-field>
              </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
              <h5>Упаковка:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number" name="order[_packing_cost]"
                            v-model.number="orderData.packing_cost"
                            :step=".01"
                            :min="0">
                  </md-input>
                  <span class="md-suffix">UAH</span>
                </md-field>
              </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
              <h5>Доставка:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number" name="order[_shipping_cost]"
                            v-model.number="orderData.shipping_cost"
                            :step=".01"
                            :min="0">
                  </md-input>
                  <span class="md-suffix">UAH</span>
                </md-field>
              </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
              <h5>Скидка:</h5>
              <div class="md-field__wrap">
                <md-field>
                  <md-input type="number"
                            name="order[_order_discount]"
                            v-model.number="orderData.discount"
                            :step=".01"
                            :min="0">
                  </md-input>
                  <span class="md-suffix">UAH</span>
                </md-field>
              </div>
            </md-list-item>
          </md-list>
        </md-list-item>
      </md-list>
    </md-toolbar>

    <md-dialog :md-active.sync="showDialog">
      <md-progress-bar md-mode="indeterminate" v-show="inProcess"></md-progress-bar>
      <md-dialog-title>Выбор товара</md-dialog-title>
      <md-dialog-content>
        <md-field>
          <md-icon>search</md-icon>
          <label>Введите название товара</label>
          <md-input v-model="search"></md-input>
        </md-field>

        <md-table v-model="searchItems" md-card @md-selected="onSelect" md-card md-fixed-header>
          <md-table-toolbar>
            <div class="md-title">Найденные товары</div>
          </md-table-toolbar>

          <md-table-toolbar slot="md-table-alternate-header" slot-scope="{ count }">
            <div class="md-toolbar-section-start">{{ getAlternateLabel(count) }}</div>

            <div class="md-toolbar-section-end">
              <md-button class="md-raised md-primary" @click="addItems()">Добавить</md-button>
            </div>
          </md-table-toolbar>

          <md-table-row slot="md-table-row" slot-scope="{ item,index }"
                        md-selectable="multiple">
            <md-table-cell md-label="ID" md-sort-by="id">
              {{ item.id }}
              <input type="hidden" :name="'fs_products['+index+'][ID]'"
                     :value="item.id">
            </md-table-cell>
            <md-table-cell md-label="Фото" md-sort-by="thumbnail_url">
              <md-avatar class="md-large">
                <img :src="item.thumbnail_url" :alt="item.title" width="100" v-if="item.thumbnail_url">
              </md-avatar>

            </md-table-cell>
            <md-table-cell md-label="Название" md-sort-by="name">{{ item.title }}</md-table-cell>
            <md-table-cell md-label="Цена" md-sort-by="price">{{ item.price }} {{ item.currency }}</md-table-cell>
            <md-table-cell md-label="К-во" md-sort-by="count">
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
        <md-button class="md-primary" @click="closeDialog()">Закрыть</md-button>
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
      orderData: this.order
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

      return `${count} товар${plural} выбрано`
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
  computed: {
    totalAmount() {
      let amount = this.orderData.cart_cost;
      amount += this.orderData.packing_cost
      amount += this.orderData.shipping_cost
      amount -= this.orderData.discount

      return amount.toFixed(2);
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