<template>
  <div class="erp-stock-dash">
    <!-- Header -->
    <header class="dash-header">
      <div class="header-content">
        <div class="header-left">
          <router-link to="/erp" class="back-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          </router-link>
          <div>
            <h1>Gestão de Estoque</h1>
            <p class="subtitle">Depósitos · Lotes · Curva ABC · Alertas</p>
          </div>
        </div>
        <div class="header-actions">
          <button class="btn btn-outline" @click="openTransferModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Transferir
          </button>
          <button class="btn btn-primary" @click="openAdjustModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
            Ajuste de Inventário
          </button>
        </div>
      </div>
    </header>

    <!-- KPI Cards -->
    <section class="kpi-grid">
      <div v-for="kpi in kpis" :key="kpi.label" class="kpi-card" :class="kpi.alert ? 'kpi-alert' : ''">
        <div class="kpi-icon" :style="{ background: kpi.bg }">
          <span v-html="kpi.icon"></span>
        </div>
        <div class="kpi-body">
          <span class="kpi-value">{{ kpi.value }}</span>
          <span class="kpi-label">{{ kpi.label }}</span>
        </div>
        <div v-if="kpi.alert" class="kpi-badge">Atenção</div>
      </div>
    </section>

    <!-- Main Grid -->
    <div class="main-grid">
      <!-- ABC Curve Chart -->
      <div class="panel">
        <div class="panel-header">
          <h2>Curva ABC — Top Produtos</h2>
          <div class="legend">
            <span class="legend-dot a">A</span> Alto impacto
            <span class="legend-dot b">B</span> Médio
            <span class="legend-dot c">C</span> Baixo
          </div>
        </div>
        <div class="abc-list">
          <div v-for="p in abcProducts" :key="p.id" class="abc-row">
            <span class="abc-badge" :class="p.curve.toLowerCase()">{{ p.curve }}</span>
            <div class="abc-info">
              <span class="abc-name">{{ p.name }}</span>
              <span class="abc-sku">SKU {{ p.sku }}</span>
            </div>
            <div class="abc-bar-wrap">
              <div class="abc-bar" :style="{ width: p.pct + '%', background: abcColor(p.curve) }"></div>
            </div>
            <span class="abc-pct">{{ p.pct }}%</span>
            <span class="abc-qty" :class="p.qty <= p.min ? 'low' : ''">{{ p.qty }} un</span>
          </div>
        </div>
      </div>

      <!-- Low Stock Alerts -->
      <div class="panel">
        <div class="panel-header">
          <h2>⚠️ Alertas de Estoque Baixo</h2>
          <span class="badge-count">{{ lowStockItems.length }}</span>
        </div>
        <div class="alert-list">
          <div v-for="item in lowStockItems" :key="item.id" class="alert-row">
            <div class="alert-icon">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 20h20L12 2zm0 3.5l7.5 13h-15L12 5.5zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z"/></svg>
            </div>
            <div class="alert-info">
              <strong>{{ item.name }}</strong>
              <small>Mín: {{ item.min }} | Atual: <span class="qty-low">{{ item.qty }}</span></small>
            </div>
            <button class="btn-repor" @click="requestReplenishment(item)">Repor</button>
          </div>
          <p v-if="!lowStockItems.length" class="empty-state">✅ Nenhum produto abaixo do mínimo.</p>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="panel mt-6">
      <div class="panel-header">
        <h2>Inventário por Localização</h2>
        <div class="search-bar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="searchQuery" placeholder="Buscar produto ou lote..." />
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Produto</th><th>Lote</th><th>Depósito</th><th>Corredor</th><th>Prateleira</th><th>Qtd</th><th>Val. Lote</th><th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="lot in filteredLots" :key="lot.id" :class="lot.qty <= 5 ? 'row-alert' : ''">
              <td>
                <div class="cell-product">
                  <span class="product-dot" :style="{ background: lot.color }"></span>
                  {{ lot.product }}
                </div>
              </td>
              <td><code class="lot-code">{{ lot.lot }}</code></td>
              <td>{{ lot.warehouse }}</td>
              <td>{{ lot.aisle }}</td>
              <td>{{ lot.shelf }}</td>
              <td>
                <span :class="lot.qty <= 5 ? 'qty-low bold' : 'qty-ok'">{{ lot.qty }}</span>
              </td>
              <td>{{ lot.expiry || '—' }}</td>
              <td>
                <div class="action-btns">
                  <button class="tbl-btn move" title="Mover" @click="transferLot(lot)">↔</button>
                  <button class="tbl-btn adj" title="Ajustar" @click="adjustLot(lot)">✎</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="!filteredLots.length" class="empty-state">Nenhum resultado encontrado.</p>
      </div>
    </div>

    <!-- Transfer Modal -->
    <div v-if="showTransfer" class="modal-overlay" @click.self="showTransfer = false">
      <div class="modal">
        <h3>Transferência de Estoque</h3>
        <div class="form-group">
          <label>Produto</label>
          <select v-model="transfer.productId">
            <option v-for="p in lots" :key="p.id" :value="p.id">{{ p.product }} ({{ p.lot }})</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Depósito Origem</label>
            <input v-model="transfer.from" placeholder="Ex: CD-01" />
          </div>
          <div class="form-group">
            <label>Depósito Destino</label>
            <input v-model="transfer.to" placeholder="Ex: CD-02" />
          </div>
        </div>
        <div class="form-group">
          <label>Quantidade</label>
          <input v-model.number="transfer.qty" type="number" min="1" />
        </div>
        <div class="modal-actions">
          <button class="btn btn-outline" @click="showTransfer = false">Cancelar</button>
          <button class="btn btn-primary" @click="confirmTransfer">Confirmar Transferência</button>
        </div>
      </div>
    </div>

    <!-- Adjust Modal -->
    <div v-if="showAdjust" class="modal-overlay" @click.self="showAdjust = false">
      <div class="modal">
        <h3>Ajuste de Inventário</h3>
        <div class="form-group">
          <label>Produto / Lote</label>
          <select v-model="adjust.lotId">
            <option v-for="l in lots" :key="l.id" :value="l.id">{{ l.product }} – {{ l.lot }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Nova Quantidade</label>
          <input v-model.number="adjust.qty" type="number" min="0" />
        </div>
        <div class="form-group">
          <label>Motivo</label>
          <textarea v-model="adjust.reason" rows="3" placeholder="Ex: Quebra, devolução, contagem física..."></textarea>
        </div>
        <div class="modal-actions">
          <button class="btn btn-outline" @click="showAdjust = false">Cancelar</button>
          <button class="btn btn-primary" @click="confirmAdjust">Salvar Ajuste</button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <transition name="toast-slide">
      <div v-if="toast.show" class="toast" :class="toast.type">{{ toast.msg }}</div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

/* ───────────── KPIs ───────────── */
const kpis = ref([
  { label: 'SKUs Ativos',      value: '0',  bg: 'linear-gradient(135deg,#6366f1,#8b5cf6)', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="2" y="3" width="20" height="18" rx="2"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="17" x2="11" y2="17"/></svg>' },
  { label: 'Lotes em Estoque', value: '0',    bg: 'linear-gradient(135deg,#0ea5e9,#38bdf8)', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>' },
  { label: 'Depósitos',        value: '0',      bg: 'linear-gradient(135deg,#10b981,#34d399)', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="16 7 12 3 8 7"/></svg>' },
  { label: 'Estoque Crítico',  value: '0',     bg: 'linear-gradient(135deg,#ef4444,#f87171)', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>', alert: true },
]);

/* ───────────── ABC ───────────── */
const abcProducts = ref([]);

const abcColor = (curve) => {
  const colors = { A: '#6366f1', B: '#f59e0b', C: '#94a3b8' };
  return colors[curve] || '#94a3b8';
};

/* ───────────── Low Stock ───────────── */
const lowStockItems = computed(() =>
  abcProducts.value.filter(p => p.qty <= p.min)
);

/* ───────────── Lots Table ───────────── */
const lots = ref([]);

const searchQuery = ref('');
const filteredLots = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return lots.value;
  return lots.value.filter(l =>
    l.product.toLowerCase().includes(q) || l.lot.toLowerCase().includes(q)
  );
});

const loadStockData = async () => {
  try {
    const [resLots, resLocations, resStock, resAbc] = await Promise.all([
      axios.get('/api/v1/erp/stock/lots').catch(() => ({ data: [] })),
      axios.get('/api/v1/erp/stock/locations').catch(() => ({ data: [] })),
      axios.get('/api/v1/erp/stock').catch(() => ({ data: [] })),
      axios.get('/api/v1/erp/stock/abc-curve').catch(() => ({ data: [] }))
    ]);

    const fetchedLots = resLots.data;
    const fetchedLocations = resLocations.data;
    const fetchedStock = resStock.data;

    // Pair lots with locations dynamically
    lots.value = fetchedLots.map((l, index) => {
      const loc = fetchedLocations[index] || fetchedLocations[0] || { warehouse: 'CD-01', aisle: 'A1', shelf: 'P3' };
      return {
        id: l.id,
        product: l.product?.name || 'Desconhecido',
        lot: l.lot_number,
        warehouse: loc.warehouse,
        aisle: loc.aisle,
        shelf: loc.shelf,
        qty: l.quantity,
        expiry: l.expiry_date ? new Date(l.expiry_date).toLocaleDateString('pt-BR') : null,
        color: index % 2 === 0 ? '#6366f1' : '#8b5cf6'
      };
    });

    // Update ABC Products based on ABC curve or actual products
    abcProducts.value = resAbc.data.length > 0 ? resAbc.data.map(item => ({
      id: item.product_id,
      name: item.name,
      sku: item.sku,
      curve: item.class,
      pct: item.percentage,
      qty: fetchedStock.find(s => s.product_id === item.product_id)?.quantity || 0,
      min: 10
    })) : fetchedStock.map((s, idx) => ({
      id: s.product_id,
      name: s.product?.name || 'Desconhecido',
      sku: s.product?.sku || '-',
      curve: idx === 0 ? 'A' : 'B',
      pct: idx === 0 ? 78 : 65,
      qty: s.quantity,
      min: idx === 0 ? 10 : 20
    }));

    // Update KPIs dynamically
    const skusCount = fetchedStock.length;
    const lotsCount = fetchedLots.length;
    const warehousesCount = new Set(fetchedLocations.map(loc => loc.warehouse)).size;
    const criticalStockCount = fetchedStock.filter(s => s.quantity <= 10).length;

    kpis.value[0].value = skusCount.toString();
    kpis.value[1].value = lotsCount.toString();
    kpis.value[2].value = warehousesCount.toString();
    kpis.value[3].value = criticalStockCount.toString();

  } catch (err) {
    console.error("Erro ao carregar dados do estoque", err);
  }
};

onMounted(() => {
  loadStockData();
});

/* ───────────── Transfer Modal ───────────── */
const showTransfer = ref(false);
const transfer = ref({ productId: null, from: '', to: '', qty: 1 });

const openTransferModal = () => { showTransfer.value = true; };
const transferLot = (lot) => {
  transfer.value.productId = lot.id;
  transfer.value.from = lot.warehouse;
  showTransfer.value = true;
};
const confirmTransfer = () => {
  showToast(`Transferência de ${transfer.value.qty} un confirmada!`, 'success');
  showTransfer.value = false;
};

/* ───────────── Adjust Modal ───────────── */
const showAdjust = ref(false);
const adjust = ref({ lotId: null, qty: 0, reason: '' });

const openAdjustModal = () => { showAdjust.value = true; };
const adjustLot = (lot) => { adjust.value.lotId = lot.id; showAdjust.value = true; };
const confirmAdjust = () => {
  const lot = lots.value.find(l => l.id === adjust.value.lotId);
  if (lot) lot.qty = adjust.value.qty;
  showToast(`Inventário ajustado com sucesso!`, 'success');
  showAdjust.value = false;
};

/* ───────────── Replenishment ───────────── */
const requestReplenishment = (item) => {
  showToast(`Solicitação de reposição para "${item.name}" enviada!`, 'info');
};

/* ───────────── Toast ───────────── */
const toast = ref({ show: false, msg: '', type: 'success' });
const showToast = (msg, type = 'success') => {
  toast.value = { show: true, msg, type };
  setTimeout(() => { toast.value.show = false; }, 3500);
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.erp-stock-dash {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  background: #0f172a;
  color: #e2e8f0;
  padding: 0 0 48px;
}

/* Header */
.dash-header {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border-bottom: 1px solid #1e293b;
  padding: 24px 32px;
  position: sticky; top: 0; z-index: 100;
  backdrop-filter: blur(12px);
}
.header-content { display: flex; align-items: center; justify-content: space-between; max-width: 1400px; margin: 0 auto; }
.header-left { display: flex; align-items: center; gap: 16px; }
.back-btn {
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 8px;
  background: #1e293b; border: 1px solid #334155; cursor: pointer;
  color: #94a3b8; text-decoration: none; transition: all .2s;
}
.back-btn:hover { background: #334155; color: #e2e8f0; }
.back-btn svg { width: 18px; height: 18px; }
h1 { font-size: 1.5rem; font-weight: 700; color: #f8fafc; }
.subtitle { font-size: .85rem; color: #64748b; margin-top: 2px; }
.header-actions { display: flex; gap: 12px; }

/* Buttons */
.btn { display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-size: .875rem; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.btn svg { width: 16px; height: 16px; }
.btn-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.35); }
.btn-outline { background: transparent; border: 1px solid #334155; color: #94a3b8; }
.btn-outline:hover { border-color: #6366f1; color: #a5b4fc; }

/* KPI Grid */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; max-width: 1400px; margin: 28px auto; padding: 0 32px; }
.kpi-card {
  background: #1e293b; border: 1px solid #334155; border-radius: 16px;
  padding: 20px; display: flex; align-items: center; gap: 16px; position: relative;
  transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.3); }
.kpi-alert { border-color: #ef444440; background: linear-gradient(135deg, #1e293b, #1f1122); }
.kpi-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-icon svg { width: 24px; height: 24px; }
.kpi-body { display: flex; flex-direction: column; }
.kpi-value { font-size: 1.75rem; font-weight: 700; color: #f8fafc; line-height: 1; }
.kpi-label { font-size: .8rem; color: #64748b; margin-top: 4px; }
.kpi-badge { position: absolute; top: 12px; right: 12px; background: #ef4444; color: #fff; font-size: .7rem; font-weight: 600; padding: 2px 8px; border-radius: 20px; }

/* Panels */
.main-grid { display: grid; grid-template-columns: 3fr 2fr; gap: 20px; max-width: 1400px; margin: 0 auto; padding: 0 32px; }
.panel { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 24px; overflow: hidden; }
.mt-6 { margin: 20px 32px 0; max-width: calc(1400px - 0px); margin-left: auto; margin-right: auto; }
.panel-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 8px; }
.panel-header h2 { font-size: 1rem; font-weight: 600; color: #f8fafc; }

/* Legend */
.legend { display: flex; align-items: center; gap: 10px; font-size: .78rem; color: #64748b; }
.legend-dot { width: 18px; height: 18px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .7rem; color: #fff; }
.legend-dot.a { background: #6366f1; }
.legend-dot.b { background: #f59e0b; }
.legend-dot.c { background: #94a3b8; }

/* ABC List */
.abc-list { display: flex; flex-direction: column; gap: 12px; }
.abc-row { display: flex; align-items: center; gap: 12px; }
.abc-badge { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
.abc-badge.a { background: #6366f120; color: #818cf8; border: 1px solid #6366f140; }
.abc-badge.b { background: #f59e0b20; color: #fbbf24; border: 1px solid #f59e0b40; }
.abc-badge.c { background: #94a3b820; color: #94a3b8; border: 1px solid #94a3b840; }
.abc-info { display: flex; flex-direction: column; min-width: 150px; }
.abc-name { font-size: .85rem; font-weight: 500; color: #e2e8f0; }
.abc-sku { font-size: .73rem; color: #475569; }
.abc-bar-wrap { flex: 1; height: 8px; background: #0f172a; border-radius: 4px; overflow: hidden; }
.abc-bar { height: 100%; border-radius: 4px; transition: width .6s ease; }
.abc-pct { font-size: .8rem; color: #94a3b8; min-width: 38px; text-align: right; }
.abc-qty { font-size: .82rem; font-weight: 600; min-width: 50px; text-align: right; }
.abc-qty.low { color: #f87171; }

/* Alert List */
.badge-count { background: #ef4444; color: #fff; font-size: .75rem; font-weight: 700; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.alert-list { display: flex; flex-direction: column; gap: 10px; }
.alert-row { display: flex; align-items: center; gap: 12px; padding: 12px; background: #0f172a; border: 1px solid #ef444430; border-radius: 10px; }
.alert-icon { width: 32px; height: 32px; background: #ef444420; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.alert-icon svg { width: 18px; height: 18px; fill: #f87171; }
.alert-info { flex: 1; display: flex; flex-direction: column; }
.alert-info strong { font-size: .87rem; color: #f8fafc; }
.alert-info small { font-size: .75rem; color: #64748b; margin-top: 2px; }
.qty-low { color: #f87171; font-weight: 700; }
.btn-repor { padding: 6px 14px; border-radius: 8px; background: #6366f120; border: 1px solid #6366f140; color: #a5b4fc; font-size: .8rem; font-weight: 600; cursor: pointer; transition: all .2s; }
.btn-repor:hover { background: #6366f1; color: #fff; }

/* Search */
.search-bar { display: flex; align-items: center; gap: 8px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 8px 12px; }
.search-bar svg { width: 16px; height: 16px; color: #475569; flex-shrink: 0; }
.search-bar input { background: none; border: none; outline: none; color: #e2e8f0; font-size: .875rem; width: 200px; }

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1px solid #334155; }
th { padding: 10px 14px; font-size: .78rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .05em; text-align: left; }
td { padding: 12px 14px; font-size: .855rem; color: #cbd5e1; border-bottom: 1px solid #1e293b; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: #ffffff05; }
.row-alert td { background: #ef444408; }
.cell-product { display: flex; align-items: center; gap: 8px; }
.product-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.lot-code { font-family: monospace; background: #0f172a; padding: 2px 8px; border-radius: 4px; font-size: .8rem; color: #94a3b8; }
.qty-low { color: #f87171; }
.qty-ok { color: #34d399; }
.bold { font-weight: 700; }
.action-btns { display: flex; gap: 4px; }
.tbl-btn { width: 28px; height: 28px; border-radius: 6px; border: none; cursor: pointer; font-size: .9rem; transition: all .2s; }
.tbl-btn.move { background: #6366f120; color: #a5b4fc; }
.tbl-btn.move:hover { background: #6366f1; color: #fff; }
.tbl-btn.adj { background: #f59e0b20; color: #fbbf24; }
.tbl-btn.adj:hover { background: #f59e0b; color: #fff; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 200; }
.modal { background: #1e293b; border: 1px solid #334155; border-radius: 20px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 24px 60px rgba(0,0,0,.4); }
.modal h3 { font-size: 1.1rem; font-weight: 700; color: #f8fafc; margin-bottom: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
label { font-size: .8rem; font-weight: 500; color: #94a3b8; }
input, select, textarea { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #e2e8f0; font-size: .875rem; outline: none; font-family: inherit; transition: border-color .2s; }
input:focus, select:focus, textarea:focus { border-color: #6366f1; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

/* Toast */
.toast { position: fixed; bottom: 28px; right: 28px; padding: 14px 24px; border-radius: 12px; font-size: .875rem; font-weight: 600; z-index: 999; box-shadow: 0 8px 24px rgba(0,0,0,.3); }
.toast.success { background: #10b981; color: #fff; }
.toast.info { background: #6366f1; color: #fff; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all .3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { transform: translateX(100px); opacity: 0; }

.empty-state { text-align: center; color: #475569; font-size: .875rem; padding: 24px 0; }

@media (max-width: 1024px) {
  .main-grid { grid-template-columns: 1fr; }
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .kpi-grid { grid-template-columns: 1fr; }
  .dash-header { padding: 16px; }
  .header-actions { display: none; }
}
</style>
