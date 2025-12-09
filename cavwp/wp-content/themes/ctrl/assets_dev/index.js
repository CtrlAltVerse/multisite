import Alpine from 'alpinejs'
import cav from '@ctrlaltvers/alpine'
import persist from '@alpinejs/persist'
import mask from '@alpinejs/mask'

Alpine.plugin(mask)
Alpine.plugin(persist)
Alpine.plugin(cav)

window.Alpine = Alpine

Alpine.data('cav', function () {
   return {
      tabletop: 0,
      web: 0,
      print: 0,

      init() {
         this.$watch('tabletop', (position) => {
            this.shiftLeft('tabletop', position)
         })
         this.$watch('web', (position) => {
            this.shiftLeft('web', position)
         })
         this.$watch('print', (position) => {
            this.shiftLeft('print', position)
         })

         if (typeof hljs !== 'undefined') {
            this.highlight()
         }

         document.querySelectorAll('.code-to-copy code').forEach((code) => {
            code.addEventListener('click', (e) => this.copyCode(e))
         })
      },

      shiftLeft(element, position) {
         const el = document.getElementById(`${element}-list`)

         el.style.left = `-${position * 100}vw`
      },

      highlight() {
         hljs.highlightAll()

         this.$do(
            'before',
            '.wp-block-code code',
            '<button class="code-copy" x-on:click="copyCode"><i class="ri-file-copy-line pointer-events-none"></i></button>'
         )
      },

      copyCode(e) {
         const content =
            e.target.tagName === 'BUTTON'
               ? e.target.nextSibling.textContent
               : e.target.textContent
         this.$do({ action: 'copy', content })
         this.$do({ action: 'toast', content: 'Código copiado' })
      },
   }
})

Alpine.start()
