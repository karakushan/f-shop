<template>
    <div class="fs-attributes">
        <div class="fs-attributes__add">
            <select class="fs-attributes__select" x-model="selectedAttribute">
                <option value="addNew">Додати новий атрибут</option>
                <option :value="attribute.term_id" v-for="attribute in attributes">{{ attribute.name }}</option>
            </select>
            <button href="#" class="button button-primary button-large"
                    v-on:click.prevent="()=>{if(selectedAttribute==='addNew') showAddForm=true}">Додати
            </button>
        </div>
        <div class="fs-attributes__create fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap"
             x-show="showAddForm"
             x-transition>
            <div class="fs-attributes__create-field fs-width-1-4">
                <label for="">Назва</label>
                <input type="text" class="fs-attributes__input" x-model="createAttribute.name">
            </div>
            <div class="fs-attributes__create-field fs-flex-1">
                <label for="">Значення</label>
                <input type="text" class="fs-attributes__input"
                       x-model="createAttribute.value"
                       placeholder="можна додати декілька значень, приклад: синій|червоний ">
            </div>
            <div class="fs-attributes__create-field">
                <div>&nbsp;</div>
                <button href="#" class="button button-primary button-large"
                        v-on:click.prevent="addAttribute()"><?php _e( 'Зберегти', 'f-shop' ) ?></button>
            </div>

            <!--Here we show the errors that occur when adding attributes-->
            <ul class="fs-width-100 fs-m-0" x-show="addErrors.length>0">
                    <li x-text="error" class="fs-field__error" v-for="(error,index) in addErrors" :key="'error-'+index"></li>
            </ul>

        </div>

        <div class="fs-attributes__list">
            <div class="fs-attributes__item " x-data="{ open:false }" v-for="attribute in attributes"
                 :key="'attribute-'+attribute.term_id">
                <div class="fs-attributes__item-header fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap">
                    <div class="fs-attributes__item-name fs-flex fs-flex-1 fs-flex-items-center fs-gap-0-5">
                        <span class="dashicons dashicons-category"></span>
                        <span x-text="attribute.name+' ('+attribute.children.length+')'"></span>
                    </div>
                    <div class="fs-attributes__item-actions">
                        <button class="fs-text-error" title="Видалити атрибут і всі його значення"
                                v-on:click.prevent="detachAttribute(attributeId)"><span
                                class="dashicons dashicons-trash"></span></button>
                        <button
                                v-on:click.prevent="open=!open"><span
                                class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                </div>
                <div class="fs-attributes__item-values" x-show="open" x-transition>
                    <div v-for="value in attribute.children"
                         :key="'value-'+value.term_id"
                         class="fs-attributes__item-value fs-flex fs-flex-items-center fs-flex-beetween fs-flex-wrap">
                        <div class="fs-attributes__item-value-name fs-flex-1">
                            <span x-text="value.name"></span>
                        </div>
                        <div class="fs-attributes__item-value-actions">
                            <button class="fs-text-error" title="Видалити значення"
                                    v-on:click.prevent="detachAttribute(value.term_id)"><span
                                    class="dashicons dashicons-trash"></span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    name: "ProductAttributes",
    data() {
        return {
            attributes: this.$props.attributes,
            selectedAttribute: '',
            showAddForm: false,
            createAttribute: {
                name: '',
                value: ''
            },
            addErrors: []
        }
    },
    props: {
        postId: {
            type: Number,
            required: true
        },
        attributes: {
            type: Array,
            required: true
        }
    },
}
</script>

<style scoped>

</style>