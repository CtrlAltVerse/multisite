<script>
   document.addEventListener('alpine:init', () => {
      Alpine.data('toolMetatags', function() {
         return {
            metas: this.$persist({
               title: '',
               description: '',
               img: '',
               type: '',
               site: '',
               url: '',
               twCard: '',
               twSite: '',
               twCreator: '',
            }).as('tools-metatags-metas'),
            output: '',

            init() {
               this.$watch('metas', this.updateOutput.bind(this))
               this.updateOutput()
            },

            domain(url, pieces = ['hostname']) {
               if (0 === url.length) {
                  return ''
               }


               const urlO = new URL(url)
               return pieces.map((piece) => {
                  return urlO[piece] + (piece === 'protocol' ? '//' : '')
               }).join('')
            },

            updateOutput() {
               let output = []

               if (this.metas.title.length) {
                  output.push(`<title>${this.metas.title}</title>`)
                  output.push(`<meta property="og:title" content="${this.metas.title}" />`)
               }

               if (this.metas.description.length) {
                  output.push(`<meta name="description" content="${this.metas.description}" />`)
                  output.push(`<meta property="og:description" content="${this.metas.description}" />`)
               }

               if (this.metas.img.length) {
                  output.push(`<meta property="og:image" content="${this.metas.img}" />`)
               }

               if (this.metas.url.length) {
                  output.push(`<meta property="og:url" content="${this.metas.url}" />`)
               }

               if (this.metas.type.length) {
                  output.push(`<meta property="og:type" content="${this.metas.type}" />`)
               }

               if (this.metas.site.length) {
                  output.push(`<meta property="og:site_name" content="${this.metas.site}" />`)
               }

               if (this.metas.twSite.length) {
                  output.push(`<meta name="twitter:site" content="@${this.metas.twSite.replace(/@/g, '')}" />`)
               }

               if (this.metas.twCreator.length) {
                  output.push(`<meta name="twitter:creator" content="@${this.metas.twCreator.replace(/@/g, '')}" />`)
               }

               if (this.metas.twCard.length) {
                  output.push(`<meta name="twitter:card" content="${this.metas.twCard}" />`)
               }

               //output.push(`<meta name="author" content="John Doe" />`)

               this.output = output.join('\n')
            },
         }
      })
   })
</script>
<div x-data="toolMetatags" class="flex gap-4 flex-col lg:flex-row">
   <form class="flex flex-col gap-3 lg:w-1/3">
      <div class="flex flex-col gap-0.5">
         <label class="font-semibold" for="title">Título</label>
         <p class="text-xs">
            Título do conteúdo. Veja boas práticas em
            <a href="https://developers.google.com/search/docs/appearance/title-link?hl=pt-br"
               title="Dicas para bons títulos por Google" target="_blank" rel="help" tabindex="-1">Google</a>
            e
            <a href="https://yandex.com/support/webmaster/search-results/title.html"
               title="Dicas para bons títulos por Yandex" target="_blank" rel="help" tabindex="-1">Yandex</a>.<br>
            Com <span x-text="metas.title.length"></span> caracteres. Recomendado: até 70
            caracteres.
         </p>
         <input id="title" class="input mt-1 w-full" type="text" x-model.trim="metas.title" />
      </div>

      <div class="flex flex-col gap-0.5">
         <label class="font-semibold" for="description">Descrição</label>
         <p class="text-xs">
            Resumo do conteúdo. Veja boas práticas
            em
            <a href="https://developers.google.com/search/docs/appearance/snippet?hl=pt-br#meta-descriptions"
               title="Dicas para boas descrições por Google" target="_blank" rel="help" tabindex="-1">Google</a>
            e
            <a href="https://yandex.com/support/webmaster/indexing-options/description.html"
               title="Dicas para boas descrições por Yandex" target="_blank" rel="help"
               tabindex="-1">Yandex</a>.<br>
            Com <span x-text="metas.description.length"></span> caracteres. Recomendado: até 200
            caracteres.
         </p>
         <textarea id="description" class="input w-full" x-model.trim="metas.description"></textarea>
      </div>

      <div class="flex flex-col gap-0.5">
         <label class="font-semibold" for="site">Site</label>
         <p class="text-xs">
            Nome do site. Veja boas práticas em <a
               href="https://developers.google.com/search/docs/appearance/site-names?hl=pt-br"
               title="Dicas para nome do site por Google" target="_blank" rel="help" tabindex="-1">Google</a>
            <br>
            Com <span x-text="metas.site.length"></span> caracteres.
         </p>
         <input id="site" class="input mt-1 w-full" type="text" x-model.trim="metas.site" />
      </div>

      <div class="flex flex-col gap-0.5">
         <label class="font-semibold" for="url">URL</label>
         <p class="text-xs">
            Endereço da página.
         </p>
         <input id="url" class="input mt-1 w-full" type="url" x-model.validate.trim="metas.url" />
      </div>

      <div class="flex flex-col gap-0.5">
         <label class="font-semibold" for="img">Imagem</label>
         <p class="text-xs">
            Endereço absoluto de imagem que ilustre o conteúdo.
         </p>
         <input id="img" class="input w-full" type="url" x-model.validate.trim="metas.img" />
      </div>

      <div class="flex flex-col gap-0.5">
         <label class="font-semibold" for="type">Tipo de conteúdo</label>
         <p class="text-xs">
            Tipo do conteúdo de acordo com o <a href="https://ogp.me/#types" target="_blank" rel="help"
               tabindex="-1">Open Graph</a>.
         </p>
         <select id="type" class="input w-full" id="ob-types" x-model.trim="metas.type">
            <option></option>
            <option>website</option>
            <option>article</option>
            <option>profile</option>
            <option>video.movie</option>
            <option>video.episode</option>
            <option>video.tv_show</option>
            <option>video.other</option>
            <option>music.song</option>
            <option>music.album</option>
            <option>music.playlist</option>
            <option>music.radio_station</option>
         </select>

      </div>

      <details>
         <summary tabindex="1">
            Opções para X/Twitter
            <p class="text-xs">Formato, site, autor.</p>
         </summary>
         <div class="pt-4 flex flex-col gap-3">
            <div class="flex flex-col gap-0.5">
               <label class="font-semibold" for="twSite">Site</label>
               <p class="text-xs">
                  Perfil que representa o site.
               </p>
               <input id="twSite" class="input w-full" type="text" x-model.trim="metas.twSite" />
            </div>
            <div class="flex flex-col gap-0.5">
               <label class="font-semibold" for="twCreator">Autor</label>
               <p class="text-xs">
                  Perfil do autor do conteúdo, geralmente um artigo. Usado em summary_large_image.
               </p>
               <input id="twCreator" class="input w-full" type="text" x-model.trim="metas.twCreator" />
            </div>
            <div class="flex flex-col gap-0.5">
               <label class="font-semibold" for="twCard">Formato</label>
               <p class="text-xs">
                  Formato da prévia no X, que pode variar de acordo com o dispositivo.
               </p>
               <div class="flex flex-col gap-2">
                  <label>
                     <div class="flex gap-1 items-center">
                        <input type="radio" value="summary" x-model.trim="metas.twCard">
                        summary
                     </div>
                     <p class="text-xs">Padrão. Imagem 1:1, mínimo 144x144.<br><a
                           href="https://developer.x.com/en/docs/x-for-websites/cards/overview/summary"
                           target="_blank" rel="help" tabindex="-1">Documentação</a>.</p>
                  </label>

                  <label>
                     <div class="flex gap-1 items-center">
                        <input type="radio" value="summary_large_image" x-model.trim="metas.twCard">
                        summary_large_image
                     </div>
                     <p class="text-xs">Imagem 2:1, mínimo 300x157.<br><a
                           href="https://developer.x.com/en/docs/x-for-websites/cards/overview/summary-card-with-large-image"
                           target="_blank" rel="help" tabindex="-1">Documentação</a>.</p>
                  </label>

                  <label>
                     <div class="flex gap-1 items-center">
                        <input type="radio" value="player" x-model.trim="metas.twCard">
                        player
                     </div>
                     <p class="text-xs">Imagem 1:1, mínimo 262x262 ou 16:9, mínimo 350x196. <a
                           href="https://developer.x.com/en/docs/x-for-websites/cards/overview/player-card"
                           target="_blank" rel="help" tabindex="-1">Documentação</a>.</p>
                  </label>

                  <label>
                     <div class="flex gap-1 items-center">
                        <input type="radio" value="app" x-model.trim="metas.twCard">
                        app
                     </div>
                     <p class="text-xs">Não aceita imagem. <a
                           href="https://developer.x.com/en/docs/x-for-websites/cards/overview/app-card"
                           target="_blank" rel="help" tabindex="-1">Documentação</a>.</p>
                  </label>

               </div>

            </div>

         </div>
      </details>

   </form>
   <div class="flex flex-col gap-6 lg:w-2/3" x-show="output.length">
      <div>
         <h2>Prévia Google</h2>
         <output class="google flex flex-col gap-1.5">
            <div class="flex gap-3 items-center">
               <img class="size-7 rounded-full border border-[#d2d2d2]" src="https://www.google.com/favicon.ico">
               <div class="w-76 text-xs">
                  <div x-text="metas.site"></div>
                  <div class="truncate" x-text="metas.url"></div>
               </div>
            </div>
            <div class="text-lg line-clamp-1 text-[#1a0dab]" x-text="metas.title"></div>
            <div class="text-xs line-clamp-2" x-text="metas.description"></div>
         </output>
      </div>
      <div>
         <h2>Prévia LinkedIn</h2>
         <p class="text-xs !mb-2">Após a publicação use o <a href="https://www.linkedin.com/post-inspector/"
               target="_blank" rel="help" tabindex="-1">validador do LinkedIn</a>.</p>
         <output class="linkedin flex items-center gap-3 border-t border-inherent">
            <div class="overflow-hidden" x-show="metas.img.length">
               <img class="object-cover w-27 aspect-[108/72]" x-bind:src="metas.img">
            </div>
            <div class="flex flex-col text-xs">
               <div class="truncate font-semibold text-sm text-black/90" x-text="metas.title"></div>
               <div class="truncate text-black/60" x-text="domain(metas.url, ['protocol','hostname'])">
               </div>
            </div>
         </output>
      </div>
      <div>
         <h2>Prévia Facebook</h2>
         <p class="text-xs !mb-2">Após a publicação use o <a href="https://developers.facebook.com/tools/debug/"
               target="_blank" rel="help" tabindex="-1">validador do Facebook</a>.</p>
         <output class="facebook flex flex-col border-t border-inherent">
            <div class="h-70 overflow-hidden" x-show="metas.img.length">
               <img class="object-cover size-full" x-bind:src="metas.img">
            </div>
            <div class="flex flex-col border border-inherent py-2.5 px-3 text-xs">
               <div class="truncate uppercase" x-text="domain(metas.url)"></div>
               <div class="truncate font-semibold text-sm color-[#1d2129]" x-text="metas.title"></div>
               <div class="truncate" x-text="metas.description"></div>
            </div>
         </output>
      </div>
      <div>
         <h2>Código para ser inserido no &lt;head&gt;</h2>
         <button class="btn mb-3" x-on:click.prevent="$do('copy','#metatags-code');$do({action:'toast',content:'Código copiado.'})" type="button">
            <i class="ri-file-copy-line"></i>
            Copiar código
            </button>
         <output id="metatags-code" class="output overflow-y-scroll max-h-80" x-text="output"></output>
      </div>
   </div>
</div>
<div class="max-w-prose">
   <h2 class="mt-8">Informações relevantes</h2>
   <p>As prévias são apenas uma estimativa.</p>
   <p>
      O <strong>título</strong> e a <strong>descrição</strong> não têm
      limite de tamanho, mas serão truncados de acordo com o dispositivo.<br>
   </p>
   <p>Os formatos de <strong>imagem</strong> suportado são JPG, PNG, WEBP e o primeiro frame de GIF. Devem ter
      menos de 5MB, com no máximo 4096x4096.</p>

   <h2 class="mt-8">Links úteis</h2>
   <ul>
      <li>
         <a href="https://developers.facebook.com/docs/sharing/webmasters/" target="_blank">
            Documentação para Facebook
         </a>
      </li>
      <li>
         <a href="https://www.linkedin.com/help/linkedin/answer/a1320408" target="_blank">
            Documentação para LinkedIn
         </a>
      </li>
      <li>
         <a href="https://www.bing.com/webmasters/help/webmasters-guidelines-30fba23a#html_tag" target="_blank">
            Boas práticas por Bing
         </a>
      </li>
   </ul>
</div>
