import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: JSON.parse(localStorage.getItem('heimdall_cart') || '[]'),
  }),

  getters: {
    totalItems: (state) => state.items.reduce((acc, i) => acc + i.quantity, 0),
    total: (state) => state.items.reduce((acc, i) => acc + i.price * i.quantity, 0),
  },

  actions: {
    addItem(product) {
      const existing = this.items.find((i) => i.product_id === product.id);
      if (existing) {
        existing.quantity++;
      } else {
        this.items.push({ product_id: product.id, name: product.name, price: product.price, quantity: 1 });
      }
      this._persist();
    },

    removeItem(productId) {
      this.items = this.items.filter((i) => i.product_id !== productId);
      this._persist();
    },

    updateQuantity(productId, qty) {
      const item = this.items.find((i) => i.product_id === productId);
      if (item) {
        item.quantity = qty;
        if (item.quantity <= 0) this.removeItem(productId);
      }
      this._persist();
    },

    clear() {
      this.items = [];
      this._persist();
    },

    _persist() {
      localStorage.setItem('heimdall_cart', JSON.stringify(this.items));
    },
  },
});
