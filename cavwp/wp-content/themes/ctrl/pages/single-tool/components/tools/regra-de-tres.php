<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolRatio', function() {
         return {
            result: '',
            highlight: '',
            pieces: {
               a1: null,
               a2: null,
               b1: null,
               b2: null,
            },

            init() {
               this.$watch('pieces', () => {
                  this.highlight = ''
                  this.result = ''

                  const pieces = [this.pieces.a1, this.pieces.b1, this.pieces.a2, this.pieces.b2].filter((p) => p > 0)
                  if (pieces.length === 3) {
                     this.calcRatio3()
                     return
                  }
                  if (pieces.length === 4) {
                     this.calcRatio4()
                     return
                  }

                  const pair1 = [this.pieces.a1, this.pieces.a2].filter((p) => p > 0)
                  if (pair1.length === 2) {
                     this.calcRatio2(...pair1)
                     return
                  }
                  const pair2 = [this.pieces.b1, this.pieces.b2].filter((p) => p > 0)
                  if (pair2.length === 2) {
                     this.calcRatio2(...pair2)
                     return
                  }


               })
            },

            calcRatio2(v1, v2) {
               let a = v1
               let b = v2
               while (b !== 0) {
                  let temp = b;
                  b = a % b;
                  a = temp;
               }

               const gcf = Math.abs(a);
               if (v1 / gcf < v1) {
                  v1 = v1 / gcf
                  v2 = v2 / gcf
               }

               this.result = `A proporção simplificada é ${v1} : ${v2}`
            },

            calcRatio3() {
               let xKey

               Object.entries(this.pieces).forEach(([key, value]) => {
                  if (value === null || value === 0) {
                     xKey = key
                  }
               })

               const xKeyA = xKey.split('')
               let mKey1 = `${xKey[0] === 'a' ? 'b' : 'a'}${xKey[1]}`
               let mKey2 = `${xKey[0]}${xKey[1] === '1' ? '2' : '1'}`
               let dKey = `${xKey[0] === 'a' ? 'b' : 'a'}${xKeyA[1] === '1' ? 2 : 1}`

               const xResult = Number((this.pieces[mKey1] * this.pieces[mKey2] / this.pieces[dKey]).toFixed(7))
               this.result = `O valor faltante é ${xResult}.`
               this.highlight = xKey
            },

            calcRatio4() {
               if (this.pieces.a1 * this.pieces.b2 === this.pieces.a2 * this.pieces.b1) {
                  return this.result = 'Correto, os valores são proporcionais.'
               } else {
                  return this.result = 'Incorreto, os valores não são proporcionais.'
               }
            },
         }
      })
   })
</script>
<form x-data="toolRatio" class="inline-flex flex-col gap-3 items-center">
   <div class="flex gap-3 items-center">
      <?php get_page_component('single-tool', 'input-ratio', ['id' => 'a1']); ?>
      :
      <?php get_page_component('single-tool', 'input-ratio', ['id' => 'a2']); ?>
   </div>
   <div class="font-semibold">é proporcional a</div>
   <div class="flex gap-3 items-center">
      <?php get_page_component('single-tool', 'input-ratio', ['id' => 'b1']); ?>
      :
      <?php get_page_component('single-tool', 'input-ratio', ['id' => 'b2']); ?>
   </div>
   <output class="font-semibold h-6.5" x-text="result"></output>
</form>
<div class="my-6">
   <h2>Como usar</h2>
   <details>
      <summary>Simplificação de proporção (2 valores)</summary>
      <p>Preencha os primeiros <strong>dois valores</strong> para calcular uma <strong>simplificação</strong> da
         proporção.</p>
      <p>Funciona melhor com números inteiros que não são primos.</p>
   </details>
   <details open>
      <summary>Regra de 3 (3 valores)</summary>
      <p>Preencha qualquer <strong>três valores</strong> para calcular o quarto valor de acordo com a
         <strong>regra de 3 simples</strong>.
      </p>
   </details>
   <details>
      <summary>Verificar proporção (4 valores)</summary>
      <p>Preencha os <strong>quatro valores</strong> para <strong>verificar</strong> se os pares são
         proporcionais.</p>
   </details>
</div>
