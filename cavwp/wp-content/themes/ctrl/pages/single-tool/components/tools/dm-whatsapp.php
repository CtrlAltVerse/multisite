<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolWaDm', function() {
         return {
            phone: '',
            msg: '',
            link: '',

            init() {
               this.$watch('phone', this.updateLink.bind(this))
               this.$watch('msg', this.updateLink.bind(this))
            },

            cleanPhone() {
               const phoneRaw = this.phone.replace(/[^0-9]/g, '')
               let parts = phoneRaw.toString().match(/5{0,2}0?([0-9]{2})9?([0-9]{8})/)

               if (!parts) {
                  return ''
               }

               let phone = ''
               const ddd = Number.parseInt(parts[1])

               if (ddd < 30) {
                  phone = '55' + ddd + '9' + parts[2]
               } else if (ddd > 30) {
                  phone = '55' + ddd + parts[2]
               }

               return phone
            },

            updateLink() {
               const phone = this.cleanPhone()
               if ((this.phone.length === 0 || phone.length === 0) && this.msg.length === 0) {
                  this.link = ''
                  return
               }

               let link = 'https://wa.me/'

               if (phone.length > 0) {
                  link += phone
               }

               if (this.msg.length > 0) {
                  link += '?text=' + encodeURI(this.msg)
               }

               this.link = link
            }
         }
      })
   })
</script>
<div x-data="toolWaDm" class="flex flex-col gap-4 lg:flex-row">
   <form class="flex flex-col gap-3 lg:w-1/3">
      <div class="flex flex-col gap-1 lg:">
         <label class="font-semibold" for="phone">Número de celular</label>
         <input id="phone" class="input" type="tel" x-model="phone" x-mask="(99) 99999-9999">
      </div>
      <div class="flex flex-col gap-1">
         <label class="font-semibold" for="msg">Mensagem inicial</label>
         <textarea id="msg" class="input" x-model="msg"></textarea>
      </div>
   </form>
   <div class="flex flex-col gap-3 lg:w-2/3" x-show="link.length" x-cloak>
      <h2>Atalho para contato</h2>
      <output id="tools-dm-wa" class="output" x-text="link"></output>
      <div class="flex gap-3">
         <a class="btn" x-bind:href="link" target="_blank">Enviar mensagem</a>
         <button class="btn" type="button" x-on:click.prevent="$do('copy', '#tools-dm-wa');$do({action:'toast',content:'Link copiado.'});">Copiar link</a>
      </div>
   </div>
</div>
