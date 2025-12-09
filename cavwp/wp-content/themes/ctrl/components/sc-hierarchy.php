<form class="code-to-copy" x-data="{template: 'archive'}">
   <select class="mb-4 py-1.5 px-2.5 bg-neutral-900" x-model="template">
      <option value="archive">Arquivos (Archives)</option>
      <option value="single">Publicações (Singles)</option>
      <option value="home">Página inicial (Home)</option>
      <option value="search">Busca (Search)</option>
      <option value="404">404</option>
   </select>
   <output class="flex flex-col gap-3 text-sm whitespace-nowrap">
      <div class="card-main" x-show="template==='archive'">
         <code class="cursor-pointer">is_archive()</code>
         <a href="https://developer.wordpress.org/reference/functions/is_archive/" target="_blank"><i class="ri-file-text-line"></i></a>
         </div>
      <div class="flex gap-2 overflow-auto pb-2" x-show="template==='archive'">
         <ul class="flex flex-col gap-2">
            <li class="card-main">
               <code class="cursor-pointer">is_author()</code>
               <a href="https://developer.wordpress.org/reference/functions/is_author/" target="_blank"><i class="ri-file-text-line"></i></a>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">author-$nicename.php</code>
               <code class="cursor-pointer">author-$nicename.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">author-$id.php</code>
               <code class="cursor-pointer">author-$id.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">author.php</code>
               <code class="cursor-pointer">author.html</code>
            </li>
         </ul>
         <ul class="flex flex-col gap-2">
            <li class="card-main">
               <code class="cursor-pointer">is_category()</code>
               <a href="https://developer.wordpress.org/reference/functions/is_category/" target="_blank"><i class="ri-file-text-line"></i></a>
               </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">category-$slug.php</code>
               <code class="cursor-pointer">category-$slug.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">category-$id.php</code>
               <code class="cursor-pointer">category-$id.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">category.php</code>
               <code class="cursor-pointer">category.html</code>
            </li>
         </ul>
         <ul class="flex flex-col gap-2">
            <li class="card-main">
               <code class="cursor-pointer">is_post_type_archive()</code>
               <a href="https://developer.wordpress.org/reference/functions/is_post_type_archive/" target="_blank"><i class="ri-file-text-line"></i></a>
               </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">archive-$posttype.php</code>
               <code class="cursor-pointer">archive-$posttype.html</code>
            </li>
         </ul>
         <ul class="flex flex-col gap-2">
            <li class="card-main">
               <code class="cursor-pointer">is_tax()</code>
               <a href="https://developer.wordpress.org/reference/functions/is_tax/" target="_blank"><i class="ri-file-text-line"></i></a>
               </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">taxonomy-$taxonomy-$term.php</code>
               <code class="cursor-pointer">taxonomy-$taxonomy-$term.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">taxonomy-$taxonomy.php</code>
               <code class="cursor-pointer">taxonomy-$taxonomy.html</code>
            </li>
         </ul>
         <ul class="flex flex-col gap-2">
            <li class="card-main">
               <code class="cursor-pointer">is_date()</code>
               <a href="https://developer.wordpress.org/reference/functions/is_date/" target="_blank"><i class="ri-file-text-line"></i></a>
               </li>
            <li>
               <ul class="flex gap-2">
                  <li class="card-main">
                     <code class="cursor-pointer">is_year()</code>
                     <a href="https://developer.wordpress.org/reference/functions/is_year/" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
                  <li class="card-main">
                     <code class="cursor-pointer">is_month()</code>
                     <a href="https://developer.wordpress.org/reference/functions/is_month/" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
                  <li class="card-main">
                     <code class="cursor-pointer">is_day()</code>
                     <a href="https://developer.wordpress.org/reference/functions/is_day/" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
               </ul>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">date.php</code>
               <code class="cursor-pointer">date.html</code>
            </li>
         </ul>
         <ul class="flex flex-col gap-2">
            <li class="card-main">
              <code class="cursor-pointer"> is_tag()</code>
               <a href="https://developer.wordpress.org/reference/functions/is_tag/" target="_blank"><i class="ri-file-text-line"></i></a>
               </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">tag-$term.php</code>
               <code class="cursor-pointer">tag-$term.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">tag-$id.php</code>
               <code class="cursor-pointer">tag-$id.html</code>
            </li>
            <li class="card-alt">
               <code class="cursor-pointer text-blue-500">tag.php</code>
               <code class="cursor-pointer">tag.html</code>
            </li>
         </ul>
      </div>
      <div class="card-alt" x-show="template==='archive'">
         <code class="cursor-pointer text-blue-500">archive.php</code>
         <code class="cursor-pointer">archive.html</code>
      </div>
      <div class="flex flex-col gap-2" x-show="template==='single'">
         <div class="flex gap-2 overflow-auto pb-2">
            <ul class="flex flex-col gap-2">
               <li class="card-main">
                  <code class="cursor-pointer">is_page()</code>
                  <a href="https://developer.wordpress.org/reference/functions/is_page/" target="_blank"><i class="ri-file-text-line"></i></a>
                  </li>
               <li class="flex gap-2">
                  <ul class="flex flex-col gap-2">
                     <li class="card-main">
                        <code class="cursor-pointer">is_page_template()</code>
                        <a href="https://developer.wordpress.org/reference/functions/is_page_template/" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">$custom.php</code>
                        <code class="cursor-pointer">$custom.html</code>
                        <a href="https://developer.wordpress.org/themes/classic-themes/templates/page-template-files/" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
                  </ul>
                  <ul class="flex flex-col gap-2">
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">page-$slug.php</code>
                        <code class="cursor-pointer">page-$slug.html</code>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">page-$id.php</code>
                        <code class="cursor-pointer">page-$id.html</code>
                     </li>
                  </ul>
               </li>
               <li class="card-alt">
                  <code class="cursor-pointer text-blue-500">page.php</code>
                  <code class="cursor-pointer">page.html</code>
               </li>
            </ul>
            <ul class="flex flex-col gap-2">
               <li class="card-main">
                  <code class="cursor-pointer">is_singular()</code>
                  <a href="https://developer.wordpress.org/reference/functions/is_singular/" target="_blank"><i class="ri-file-text-line"></i></a>
                  </li>
               <li class="flex gap-2">
                  <ul class="flex flex-col gap-2">
                     <li class="card-main">
                        <code class="cursor-pointer">is_attachment()</code>
                        <a href="https://developer.wordpress.org/reference/functions/is_attachment/" target="_blank"><i class="ri-file-text-line"></i></a>
                        </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">$mimetype-$subtype.php</code>
                        <code class="cursor-pointer">$mimetype-$subtype.html</code>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">$subtype.php</code>
                        <code class="cursor-pointer">$subtype.html</code>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">$mimetype.php</code>
                        <code class="cursor-pointer">$mimetype.html</code>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">attachment.php</code>
                        <code class="cursor-pointer">attachment.html</code>
                     </li>
                  </ul>
                  <ul class="flex flex-col gap-2">
                     <li class="card-main">
                        <code class="cursor-pointer">is_singular($cpt)</code>
                        <a href="https://developer.wordpress.org/reference/functions/is_singular/" target="_blank"><i class="ri-file-text-line"></i></a>
                        </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">$custom.php</code>
                        <code class="cursor-pointer">$custom.html</code>
                        <a href="https://developer.wordpress.org/themes/classic-themes/templates/page-template-files/#creating-page-templates-for-specific-post-types" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">single-$posttype-$slug.php</code>
                        <code class="cursor-pointer">single-$posttype-$slug.html</code>
                     </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">single-$posttype.php</code>
                        <code class="cursor-pointer">single-$posttype.html</code>
                     </li>
                  </ul>
                  <ul class="flex flex-col gap-2">
                     <li class="card-main">
                        <code class="cursor-pointer">is_single()</code>
                        <a href="https://developer.wordpress.org/reference/functions/is_single/" target="_blank"><i class="ri-file-text-line"></i></a>
                        </li>
                     <li class="card-alt">
                        <code class="cursor-pointer text-blue-500">$custom.php</code>
                        <code class="cursor-pointer">$custom.html</code>
                        <a href="https://developer.wordpress.org/themes/classic-themes/templates/page-template-files/#creating-page-templates-for-specific-post-types" target="_blank"><i class="ri-file-text-line"></i></a>
                     </li>
                     <li class="col-start-2 row-start-2 row-span-2 card-alt">
                        <code class="cursor-pointer text-blue-500">single-post.php</code>
                        <code class="cursor-pointer">single-post.html</code>
                     </li>
                  </ul>
               </li>
               <li class="card-alt">
                  <code class="cursor-pointer text-blue-500">single.php</code>
                  <code class="cursor-pointer">single.html</code>
               </li>
            </ul>
         </div>
         <div class="card-alt">
            <code class="cursor-pointer text-blue-500">singular.php</code>
            <code class="cursor-pointer">singular.html</code>
         </div>
      </div>
      <div class="flex flex-col gap-2" x-show="template==='home'">
         <div class="flex gap-2 w-full">
            <ul class="flex-1">
               <li class="card-main">
                  <code class="cursor-pointer">is_home()</code>
                  <a href="https://developer.wordpress.org/reference/functions/is_home/" target="_blank"><i class="ri-file-text-line"></i></a>
                  </li>
            </ul>
            <ul class="flex flex-col gap-2 flex-1">
               <li class="card-main">
                  <code class="cursor-pointer">is_front_page()</code>
                  <a href="https://developer.wordpress.org/reference/functions/is_front_page/" target="_blank"><i class="ri-file-text-line"></i></a>
                  </li>
               <li class="card-alt">
                  <code class="cursor-pointer text-blue-500">front-page.php</code>
                  <code class="cursor-pointer">front-page.html</code>
               </li>
               <li>
                  <ul class="flex gap-2">
                     <li class="flex-1 card-main">
                        <h3>Página com posts</h3>
                     </li>
                     <li class="flex-1 card-main">
                        <h3>Página estática</h3>
                        <button type="button" class="w-full text-left" x-on:click="template='single'">
                           Veja Página <i class="ri-arrow-right-line"></i>
                        </button>
                     </li>
                  </ul>
               </li>
            </ul>
         </div>
         <div class="card-alt">
            <code class="cursor-pointer text-blue-500">home.php</code>
            <code class="cursor-pointer">home.html</code>
         </div>
      </div>
      <div class="flex flex-col gap-2" x-show="template==='search'">
         <div class="card-main">
            <code class="cursor-pointer">is_search()</code>
            <a href="https://developer.wordpress.org/reference/functions/is_search/" target="_blank"><i class="ri-file-text-line"></i></a>
            </div>
         <div class="card-alt">
            <code class="cursor-pointer text-blue-500">search.php</code>
            <code class="cursor-pointer">search.html</code>
         </div>
      </div>
      <div class="flex flex-col gap-2" x-show="template==='404'">
         <div class="card-main">
            <code class="cursor-pointer">is_404()</code>
            <a href="https://developer.wordpress.org/reference/functions/is_404/" target="_blank"><i class="ri-file-text-line"></i></a>
            </div>
         <div class="card-alt">
            <code class="cursor-pointer text-blue-500">404.php</code>
            <code class="cursor-pointer">404.html</code>
         </div>
      </div>
      <div class="card-alt">
         <code class="cursor-pointer text-blue-500">index.php</code>
         <code class="cursor-pointer">index.html</code>
      </div>
   </output>


</form>
