<dialog id="rewardDetails"
   class="!m-auto w-full max-w-4xl rounded-lg text-neutral-100 bg-neutral-700 backdrop:z-45 backdrop:bg-neutral-900/60"
   x-on:click.prevent.self="document.querySelector('#rewardDetails').close()">
   <form class="py-4 px-5" x-on:submit.prevent="redeemReward($event.target)">
      <div class="flex gap-6">
         <img id="product_img" class="h-55 rounded" src="https://placehold.co/200" alt />
         <div class="flex flex-col gap-4">
            <hgroup class="flex flex-col gap-1">
               <h2 id="product_title" class="text-lg font-semibold">Titulo</h2>
               <p id="product_details" class="text-sm">Detalhes</p>
            </hgroup>
            <p id="product_summary" class="text-base font-medium"></p>
            <div class="flex gap-3 justify-end mt-6">
               <button class="py-2 px-4 font-semibold cursor-pointer"
                  type="button" x-on:click.prevent="document.querySelector('#rewardDetails').close()">Voltar</button>
               <a id="product_link" class="py-2 px-4 font-semibold cursor-pointer"
                  href="<?php echo home_url(); ?>"
                  type="button">Mais detalhes</a>
               <?php if (is_user_logged_in()) { ?>
                  <button id="product_submit"
                     class="py-2 px-4 bg-neutral-100 text-neutral-800 rounded font-semibold cursor-pointer"
                     type="submit">Confirmar <span id="product_value"></span></button>
               <?php } else { ?>
                  <button class="py-2 px-4 bg-neutral-100 text-neutral-800 rounded font-semibold cursor-pointer"
                     type="button">Cadastrar ou Entrar</button>
               <?php } ?>
            </div>
         </div>
      </div>
      <input name="product_reward_ID" type="hidden">
   </form>
</dialog>
