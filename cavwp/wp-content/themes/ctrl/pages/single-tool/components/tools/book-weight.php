<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolBookWeight', function() {
         return {
            cover: 300,
            block: 90,
            height: 23,
            width: 16,
            pages: 100,
            quantity: 1,
            weight: 0,
            weightTotal: '',

            init() {
               this.calc()
            },

            calc() {
               const pageSize = this.height * this.width
               const coverWeight = pageSize * 2 * this.cover / 10000
               const blockWeight = pageSize * this.pages * this.block / 10000
               this.weight = coverWeight + blockWeight

               this.weightTotal = this.weight * this.quantity
               if (this.weightTotal >= 1000) {
                  this.weightTotal = (this.weightTotal / 1000).toFixed(2) + 'kg'
               } else {
                  this.weightTotal = this.weightTotal.toFixed(1) + 'g'
               }
            }
         }
      })
   })
</script>
<form x-data="toolBookWeight">
   <div class="flex flex-col gap-4 max-w-lg mx-auto">
      <div class="form-item">
         <label for="cover">Gramatura da Capa</label>
         <div class="input">
            <input id="cover" name="cover" x-model.number="cover" x-on:input="calc" type="number" min="30" max="400" step="10" />
            <span class="post-input">g/m²</span>
         </div>
      </div>
      <div class="form-item">
         <label for="block">Gramatura do Miolo</label>
         <div class="input">
            <input id="block" name="block" x-model.number="block" x-on:input="calc" type="number" min="30" max="400" step="10" />
            <span class="post-input">g/m²</span>
         </div>
      </div>
      <div class="form-item">
         <label for="width">Largura</label>
         <div class="input">
            <input id="width" name="width" x-model.number="width" x-on:input="calc" type="number" min="1" max="60" step="0.5" />
            <span class="post-input">cm</span>
         </div>
      </div>
      <div class="form-item">
         <label for="height">Altura</label>
         <div class="input">
            <input id="height" name="height" x-model.number="height" x-on:input="calc" type="number" min="1" max="60" step="0.5" />
            <span class="post-input">cm</span>
         </div>
      </div>
      <div class="form-item">
         <label for="pages">Número de páginas</label>
         <div class="input">
         <input id="pages" name="pages" x-model.number="pages" x-on:input="calc" type="number" min="4" step="4" />
         <span class="post-input">páginas</span>
         </div>
      </div>
      <div class="form-item">
         <label for="quantity">Quantidade</label>
         <div class="input">
            <input id="quantity" name="quantity" x-model.number="quantity" x-on:input="calc" type="number" min="1" step="1" />
            <span class="post-input">unidade(s)</span>
      </div>
      <div class="flex text-lg text-center mt-4">
         <div class="flex flex-col grow">
            <strong>Peso unitário</strong>
            <output class="font-medium" x-text="`${weight.toFixed(1)}g`"></output>
         </div>
         <div class="flex flex-col grow">
            <strong>Peso total</strong>
            <output class="font-medium" x-text="weightTotal"></output>
         </div>
      </div>
   </div>
</form>
