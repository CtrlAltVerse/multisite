document.addEventListener('alpine:init', () => {
   Alpine.data('rewards', () => ({
      init() {
         this.$rest.get(`${cavRewards.endpoint}?_wpnonce=${cavRewards.nonce}`)
      },

      getReward(reward_ID) {
         this.$do({
            action: 'value',
            target: '[name="product_reward_ID"]',
            content: reward_ID,
         })

         this.$rest.get(
            `${cavRewards.restApi}/${reward_ID}?_wpnonce=${cavRewards.nonce}`
         )
      },

      redeemReward(form) {
         const formData = new FormData(form)

         this.$rest.post(`${cavRewards.restApi}?_wpnonce=${cavRewards.nonce}`)
      },
   }))
})
