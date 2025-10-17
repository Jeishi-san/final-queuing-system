class DashboardManager {
    constructor() {
        this.modal = document.getElementById('assignModal');
        this.modalClose = document.getElementById('assignModalClose');
        this.modalContainer = document.getElementById('assignFormContainer');
        this.toast = document.getElementById('toast');
        this.tableContainer = document.getElementById('ticketTableContainer');
        this.statsPanel = document.getElementById('statsPanel');
        this.filters = document.getElementById('ticketFilters');
        this.extraFilters = document.getElementById('extraFilters');
        this.refreshBtn = document.getElementById('refreshDashboardBtn');
        this.applyFiltersBtn = document.getElementById('applyFiltersBtn');
        this.clearFiltersBtn = document.getElementById('clearFiltersBtn');

        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Modal close
        this.modalClose?.addEventListener('click', () => this.closeModal());
        this.modal?.addEventListener('click', e => { if(e.target === this.modal) this.closeModal(); });

        // Dashboard refresh
        this.refreshBtn?.addEventListener('click', () => this.refreshDashboard());
        this.filters?.addEventListener('submit', e => { e.preventDefault(); this.refreshDashboard(); });
        this.extraFilters?.addEventListener('submit', e => { e.preventDefault(); this.refreshDashboard(); });

        // Clear filters
        this.clearFiltersBtn?.addEventListener('click', e => { e.preventDefault(); window.location.href = this.clearFiltersBtn.getAttribute('href'); });

        // Filter change auto-refresh
        ['statusFilter', 'itPersonnelFilter'].forEach(id => {
            const el = document.getElementById(id);
            el?.addEventListener('change', () => { 
                if(!document.activeElement.isEqualNode(this.applyFiltersBtn)) this.refreshDashboard(); 
            });
        });

        // Delegate assign button clicks
        document.addEventListener('click', e => {
            const button = e.target.closest('.open-assign');
            if(button) {
                e.preventDefault();
                const ticketId = button.getAttribute('data-id');
                if(ticketId) this.openModal(ticketId);
            }
        });

        // Delegate pagination links
        this.tableContainer.addEventListener('click', e => {
            const link = e.target.closest('.pagination a');
            if(link) {
                e.preventDefault();
                this.refreshDashboard(link.href);
            }
        });
    }

    async refreshDashboard(url = null) {
        try {
            const params = new URLSearchParams(new FormData(this.filters));
            const extraParams = new URLSearchParams(new FormData(this.extraFilters));
            const allParams = new URLSearchParams(params.toString() + '&' + extraParams.toString());

            const tableUrl = url || `/dashboard/tickets-table?${allParams.toString()}`;
            const statsUrl = `/dashboard/tickets-stats?${allParams.toString()}`;

            this.tableContainer.classList.add('opacity-50', 'pointer-events-none');

            // Fetch table and stats
            const [tableRes, statsRes] = await Promise.all([
                fetch(tableUrl, { headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' } }),
                fetch(statsUrl, { headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' } })
            ]);

            // Update table
            if(tableRes.ok) {
                const tableHTML = await tableRes.text();
                this.tableContainer.innerHTML = tableHTML;
            }

            // Update stats
            if(statsRes.ok) {
                const statsHTML = await statsRes.text();
                if(this.statsPanel) {
                    this.statsPanel.outerHTML = statsHTML;
                    // Re-select after replacing DOM
                    this.statsPanel = document.getElementById('statsPanel');
                }
            }

            this.showToast('✅ Dashboard updated');
        } catch (err) {
            console.error('❌ Dashboard refresh error:', err);
            this.showToast('⚠️ Failed to refresh dashboard', 'error');
        } finally {
            this.tableContainer.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    showToast(message, type='success'){
        if(!this.toast) return;
        this.toast.textContent = message;
        this.toast.className = `fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-lg text-white z-50 max-w-sm ${
            type==='error'?'bg-red-600':'bg-green-600'
        }`;
        this.toast.classList.remove('hidden');
        setTimeout(()=>this.toast.classList.add('hidden'),3000);
    }

    async openModal(ticketId){
        if(!ticketId){ this.showToast('⚠️ Invalid ticket ID','error'); return; }
        this.modal.classList.remove('hidden'); this.modal.classList.add('flex');
        this.modalContainer.innerHTML = `<div class="flex justify-center py-10"><div class="animate-spin h-8 w-8 border-b-2 border-blue-600 rounded-full"></div></div>`;

        try {
            const res = await fetch(`/tickets/${ticketId}/assign`, {
                headers:{ 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }
            });
            if(res.ok){
                this.modalContainer.innerHTML = await res.text();
                this.initAssignForm();
            } else throw new Error(`HTTP ${res.status}`);
        } catch(err){
            console.error(err);
            this.modalContainer.innerHTML = `<div class="text-center p-6"><p class="text-red-500 dark:text-red-400 mb-4">⚠️ Failed to load assignment form</p><button onclick="dashboard.closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">Close</button></div>`;
            this.showToast('⚠️ Failed to load form','error');
        }
    }

    closeModal(){ 
        this.modal?.classList.add('hidden'); 
        this.modal?.classList.remove('flex'); 
        this.modalContainer.innerHTML=''; 
    }

    initAssignForm(){
        const form = document.getElementById('updateTicketForm');
        if(!form) return console.error('Assign form not found');

        form.addEventListener('submit', async e=>{
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if(!submitBtn) return;

            const originalText = submitBtn.innerHTML;
            submitBtn.disabled=true;
            submitBtn.innerHTML=`<div class="flex items-center gap-2"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>Saving...</div>`;

            const formData = new FormData(form);
            if(!formData.has('_method')) formData.append('_method','PATCH');

            try {
                const res = await fetch(form.action,{
                    method:'POST',
                    headers:{ 'Accept':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body:formData
                });
                const data = await res.json();
                if(res.ok && data.success){
                    this.showToast('✅ Ticket updated successfully');
                    this.closeModal();
                    await this.refreshDashboard();
                } else throw new Error(data.message||data.errors||'Update failed');
            } catch(err){
                console.error(err);
                this.showToast(err.message||'⚠️ Failed to update ticket','error');
                submitBtn.disabled=false;
                submitBtn.innerHTML=originalText;
            }
        });

        const cancelBtn = document.getElementById('panelCancel');
        cancelBtn?.addEventListener('click',()=>this.closeModal());
    }
}

// Initialize
window.dashboard=null;
document.addEventListener('DOMContentLoaded',()=>{ window.dashboard=new DashboardManager(); });
window.openAssignModal=ticketId=>window.dashboard?.openModal(ticketId);
window.closeAssignModal=()=>window.dashboard?.closeModal();
window.refreshDashboard=()=>window.dashboard?.refreshDashboard();
