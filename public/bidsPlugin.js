/*
****** Another website produced by The Lochlite & Lochpay Company
___
|   |
|   |             _______         ______       __      __
|   |            /       \       /  ____|     |  |    |  |
|   |______     /         \     /  /          |  |----|  |
|          |    \         /     \  \____      |  |----|  |
|__________|     \_______/       \______|     |__|    |__| Lite ®


Long live Lochlite! ******
 * @api {get} /timeline bidPlugins
 * @apiDescription Função para exebir noticias.
 * @apiName bidsPlugin
 * @apiGroup Bid
 * @apiVersion 1.0.0
 * @apiAuthor Igor Macedo Montalvão | The Lochlite & Lochpay Company
*/

let container;
let shadowRoot;
let flex;
let paginationControls;
let currentPage;
let mode;
let modeauth;
let view;
let all;
let disableReadMoreOnCard;
let disableButtonViewAll;
let labelButtonViewAll;
let baseUrl;
let appid;
let token;
let stylesheet;

export default class BidsPlugin extends HTMLElement {
    constructor() {
        super();
        // Create a shadow root
        this.attachShadow({ mode: 'open' });
    }
    static get observedAttributes() {
        return ['mode','mode-auth', 'url', 'app-id', 'token'];
    }
    connectedCallback() {
        console.log('BidsPlugin element added to page.');
        this.init(this);
    }
    attributeChangedCallback = (name, oldValue, newValue) => {
        console.log('BidsPlugin attribute changed:', name, oldValue, newValue);
        if (name === 'mode') mode = newValue;
        if (name === 'mode-auth') modeauth = newValue;
        if (name === 'url') baseUrl = newValue;
        if (name === 'app-id') appid = newValue;
        if (name === 'token') token = newValue;
        this.init();
    }
    disconnectedCallback = () => {
        console.log('BidsPlugin disconnected');
    }
    adoptedCallback = () => {
        console.log('BidsPlugin adopted');
    }   
    async fetchBid(url, token) {
        try {
            this.onSkeleton();
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };

            if (modeauth === 'appid') {
                headers['X-App-ID'] = appid;
            } else {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: headers
            });
    
            const data = await response.json();
            if (!response.ok) {
                alert(`HTTP error! ref: ${data?.message}`);
                throw new Error(`HTTP error! status: ${data?.message}`);
            }
            return data;
        } catch (error) {
            console.error('Error fetching bid:', error);
            alert('Error fetching bid: ' + error?.message);
            return { data: [], last_page: 1 };
        }
    }
    createCard = (bid) => {
        disableReadMoreOnCard = container.dataset.disableReadMoreOnCard || 'false';
        const isTruncatedMode = mode === 'timeline' || mode === 'timeline.all';
        const truncatedTitle = (isTruncatedMode && bid.name.length > 130) 
        ? bid.name.substring(0, 130) + '...' 
        : bid.name;

        return `
            <a href="${view}?bid=${bid.id}" class="col-12 col-md-6 col-lg-3 text-decoration-none">
            <div class="card mb-3 text-decoration-none btn btn-outline-light border-none border-0" style="width: 100%; height: 100%;">
                <div class="card-body shadow d-flex flex-column">
                <h5 class="card-title text-decoration-none text-start">${truncatedTitle}</h5>
                <h6 class="card-subtitle mb-2 text-muted text-decoration-none text-start hidden d-none">${new Date(bid.created_at).toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' })}</h6>
                ${disableReadMoreOnCard !== 'true' ? `<p class="card-text text-truncate mt-auto text-start">Ler mais</p>` : ''}
                </div>
            </div>
            </a>
        `;
    }

    createSkeletonCard = () => {
        return `
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card mb-3" style="width: 100%; height: 100%;" aria-hidden="true">
                    <div class="card-img-top placeholder-glow" style="height: 12rem; background-color: #e9ecef;"></div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title placeholder-glow">
                            <span class="placeholder col-6"></span>
                        </h5>
                        <h6 class="card-subtitle mb-2 placeholder-glow">
                            <span class="placeholder col-4"></span>
                        </h6>
                        <p class="card-text placeholder-glow mt-auto">
                            <span class="placeholder col-7"></span>
                            <span class="placeholder col-4"></span>
                        </p>
                    </div>
                </div>
            </div>
        `;
    }
    onSkeleton = () => {
        flex.innerHTML = '';
        flex.innerHTML = Array.from({ length: 4 }, this.createSkeletonCard).join('');
    }
    createBidView = (bid) => {
        console.log(bid);
    
        const html = `
            <div class="card shadow-sm w-100">
                <div class="card-body w-100">
                    <h1 class="card-title mx-auto text-center" style="text-align: center; margin-bottom: 6px;">${bid.name}</h1>
                    <h6 class="card-subtitle text-center d-none hidden" style="display:none; margin-bottom: 3px;">
                        Publicado em: ${new Date(bid.created_at).toLocaleDateString('pt-BR', {
                            day: '2-digit', month: 'long', year: 'numeric'
                        })}
                    </h6>
                    <table class="table table-light table-bordered table-responsive table-hover w-100 mt-3" style="width: 100%;">
                        <thead style="background-color:rgb(227, 227, 227);">
                            <tr>
                                <th style="width:10%;">Arquivo</th>
                                <th style="width:15%;">Data</th>
                                <th style="width:75%;">Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${bid.files.map((file, index) => {
                                let icon;
                                switch (file.extension) {
                                    case 'pdf':
                                        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-file-earmark-pdf-fill" viewBox="0 0 16 16"><path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05 12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064.44.44 0 0 1-.06.2.3.3 0 0 1-.094.124.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z"/><path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM4.165 13.668c.09.18.23.343.438.419.207.075.412.04.58-.03.318-.13.635-.436.926-.786.333-.401.683-.927 1.021-1.51a11.7 11.7 0 0 1 1.997-.406c.3.383.61.713.91.95.28.22.603.403.934.417a.86.86 0 0 0 .51-.138c.155-.101.27-.247.354-.416.09-.181.145-.37.138-.563a.84.84 0 0 0-.2-.518c-.226-.27-.596-.4-.96-.465a5.8 5.8 0 0 0-1.335-.05 11 11 0 0 1-.98-1.686c.25-.66.437-1.284.52-1.794.036-.218.055-.426.048-.614a1.24 1.24 0 0 0-.127-.538.7.7 0 0 0-.477-.365c-.202-.043-.41 0-.601.077-.377.15-.576.47-.651.823-.073.34-.04.736.046 1.136.088.406.238.848.43 1.295a20 20 0 0 1-1.062 2.227 7.7 7.7 0 0 0-1.482.645c-.37.22-.699.48-.897.787-.21.326-.275.714-.08 1.103"/></svg>';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-file-earmark-word-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.485 6.879l1.036 4.144.997-3.655a.5.5 0 0 1 .964 0l.997 3.655 1.036-4.144a.5.5 0 0 1 .97.242l-1.5 6a.5.5 0 0 1-.967.01L8 9.402l-1.018 3.73a.5.5 0 0 1-.967-.01l-1.5-6a.5.5 0 1 1 .97-.242z"/></svg>';
                                        break;
                                    case 'xls':
                                    case 'xlsx':
                                        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-file-earmark-excel-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'webp':
                                    case 'svg':
                                    case 'jfif':
                                    case 'gif':
                                        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-file-earmark-image-fill" viewBox="0 0 16 16"><path d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707v5.586l-2.73-2.73a1 1 0 0 0-1.52.127l-1.889 2.644-1.769-1.062a1 1 0 0 0-1.222.15L2 12.292V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zm-1.498 4a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/><path d="M10.564 8.27 14 11.708V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-.293l3.578-3.577 2.56 1.536 2.426-3.395z"/></svg>';
                                        break;
                                    default:
                                        icon = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-file-earmark-medical-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m-3 2v.634l.549-.317a.5.5 0 1 1 .5.866L7 7l.549.317a.5.5 0 1 1-.5.866L6.5 7.866V8.5a.5.5 0 0 1-1 0v-.634l-.549.317a.5.5 0 1 1-.5-.866L5 7l-.549-.317a.5.5 0 0 1 .5-.866l.549.317V5.5a.5.5 0 1 1 1 0m-2 4.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1m0 2h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1"/></svg>';
                                }
    
                                // Alternância de cor (zebra)
                                const bgColor = index % 2 === 0 ? '#ffffff' : '#F9F9F9';
    
                                return `
                                    <tr style="background-color: ${bgColor};">
                                        <td style="width:10%; color: black;">
                                            <a class="btn btn-link text-decoration-none d-flex justify-content-center align-items-center"
                                                href="${baseUrl.replace('/api', '/storage/') + file.path}"
                                                target="_blank"
                                                title="${file.name}"
                                                style="color: black; display: flex; align-items: center; align-self: center; justify-content: center;" download>
                                                ${icon}
                                            </a>
                                        </td>
                                        <td style="width:15%;"><center>${new Date(file.date + 'T03:00:00').toLocaleDateString('pt-BR', {
                                            timezone: 'UTC', day: '2-digit', month: '2-digit', year: 'numeric'
                                        })}</center></td>
                                        <td style="width:75%;">${file.description || 'Sem descrição'}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    
        return html;
    };
       showSearchForm = () => {
            const searchForm = document.createElement("form");
            searchForm.classList.add("d-flex", "justify-content-center", "my-2", "w-100", "mx-auto", "px-4", "pb-1", "border-bottom", "container");
            searchForm.innerHTML = `    <div class="d-flex w-100 justify-content-between align-items-center flex-wrap">
        <div class="col-12 col-md-9 mb-2 mb-md-0">
            <div class="h3">Últimos editais</div>
        </div>
        <div class="col-12 col-md-3">
        <div class="input-group">
            <input id="searchBid" type="search" class="form-control" name="search" placeholder="Buscar edital..." aria-label="Buscar edital..." aria-describedby="button-addon2">
            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 50 50">
                <path d="M 21 3 C 11.6 3 4 10.6 4 20 C 4 29.4 11.6 37 21 37 C 24.354553 37 27.47104 36.01984 30.103516 34.347656 L 42.378906 46.621094 L 46.621094 42.378906 L 34.523438 30.279297 C 36.695733 27.423994 38 23.870646 38 20 C 38 10.6 30.4 3 21 3 z M 21 7 C 28.2 7 34 12.8 34 20 C 34 27.2 28.2 33 21 33 C 13.8 33 8 27.2 8 20 C 8 12.8 13.8 7 21 7 z"></path>
                </svg>
            </button>
        </div>
        </div>
    </div>`;
        shadowRoot.insertBefore(searchForm, shadowRoot.firstChild);
        searchForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const searchBid = searchForm.querySelector('#searchBid').value;
            const response = await this.fetchBid(`${baseUrl}/timeline-all?search=${searchBid}`, token);
            this.renderTimelineAll(response.data, currentPage, response.last_page, onPageChange);
            if(searchBid){
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('search', searchBid);
                window.history.pushState({}, '', currentUrl.toString());
            }
        });
    };
    onPageChange = async (page) => {
            currentPage = page;
            const searchParam = new URLSearchParams(window.location.search).get('search');
            const response = await this.fetchBid(`${baseUrl}/timeline-all?page=${page}${searchParam ? `&search=${searchParam}` : ''}`, token);
            this.renderTimelineAll(response.data, currentPage, response.last_page, onPageChange);
            if(searchParam){
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', page);
                currentUrl.searchParams.set('search', searchParam);
                window.history.pushState({}, '', currentUrl.toString());
                shadowRoot.getElementById('searchBid').value = searchParam;
            } else {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', page);
                window.history.pushState({}, '', currentUrl.origin + currentUrl.pathname);
            }

    }
    renderTimeline = (bids) => {
            flex.innerHTML = bids.map(this.createCard).join('');
    }    
    renderPaginationControls = (currentPage, totalPages, onPageChange) => {
            paginationControls.innerHTML = '';
            const paginationContainer = document.createElement("div");
            paginationContainer.classList.add("d-flex", "flex-wrap", "justify-content-center", "gap-2");
        
            paginationContainer.innerHTML = `
                <button class="btn btn-primary ${currentPage === 1 ? 'disabled' : ''}" 
                        ${currentPage === 1 ? 'disabled' : ''} 
                        data-page="${currentPage - 1}">Anterior</button>
                
                ${Array.from({ length: totalPages }, (_, i) => `
                    <button class="btn btn-primary ${currentPage === i + 1 ? 'disabled' : ''}" 
                            ${currentPage === i + 1 ? 'disabled' : ''} 
                            data-page="${i + 1}">${i + 1}</button>
                `).join('')}
                
                <button class="btn btn-primary ${currentPage === totalPages ? 'disabled' : ''}" 
                        ${currentPage === totalPages ? 'disabled' : ''} 
                        data-page="${currentPage + 1}">Próximo</button>
            `;
            paginationContainer.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', (event) => {
                    const page = event.target.getAttribute('data-page');
                    if (page) onPageChange(parseInt(page));
                });
            });
        
            paginationControls.appendChild(paginationContainer);
    }
    renderTimelineAll = (bids, currentPage, totalPages, onPageChange) => {
            flex.innerHTML = '';

            let lastYear = null;

            bids.forEach(bid => {
                if (bid.year !== lastYear) {
                    // Quebra a linha do grid e mostra o ano
                    flex.innerHTML += `
                        <div class="w-100">
                            <div class="year-label my-3 px-3 py-2 rounded bg-light border-start border-4 border-primary text-dark shadow-sm">
                              ${bid.year}
                            </div>
                        </div>
                    `;
                    lastYear = bid.year;
                }
                flex.innerHTML += this.createCard(bid);
            });
            this.renderPaginationControls(currentPage, totalPages, onPageChange);
    } 
    renderTimelineView = (bid) => {
        shadowRoot.innerHTML = this.createBidView(bid);
    };    
    createFlexContainer = () => {
            var div = document.createElement("div");
            div.classList.add("container", "w-100", "mx-auto", "px-4", "py-4");
            shadowRoot.appendChild(div);
            flex = document.createElement("div");
            flex.classList.add("row", "row-cols-1", "row-cols-sm-2", "row-cols-md-4", "row-cols-lg-4");
            div.appendChild(flex);
    }
    injectStyle = () => {
        var sheet = new CSSStyleSheet();
        const bootstrap = document.createElement("style");
        var bootstrapImport = `@import url(${stylesheet}); @import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');`;
        var styleCSS = `
            .with-space { white-space: pre-wrap; } 
            .shadow { box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px!important; } 
            table { width: 100%; margin-bottom: 1rem; color: #212529; border-collapse: collapse; }
            th, td { padding: 0.75rem; vertical-align: top; border-top: 1px solid #dee2e6; }
            thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; }
            tbody + tbody { border-top: 2px solid #dee2e6; }
            .table { --bs-table-bg: transparent; --bs-table-accent-bg: transparent; --bs-table-striped-color: #212529; --bs-table-striped-bg: rgba(0, 0, 0, 0.05); --bs-table-active-color: #212529; --bs-table-active-bg: rgba(0, 0, 0, 0.1); --bs-table-hover-color: #212529; --bs-table-hover-bg: rgba(0, 0, 0, 0.075); width: 100%; margin-bottom: 1rem; color: #212529; vertical-align: top; border-color: #dee2e6; }
            .table-bordered { border: 1px solid #dee2e6; }
            .table-bordered th, .table-bordered td { border: 1px solid #dee2e6; }
            .table-hover tbody tr:hover { color: #212529; background-color: rgba(0, 0, 0, 0.075); }
            .table-light { --bs-table-bg: #f8f9fa; --bs-table-striped-bg: #ecedee; --bs-table-striped-color: #000; --bs-table-active-bg: #dfe0e1; --bs-table-active-color: #000; --bs-table-hover-bg: #e5e6e7; --bs-table-hover-color: #000; color: #000; border-color: #dee2e6; }
            .year-label {font-size: 1.25rem; font-weight: 600; text-align: left;}
        `;
        bootstrap.innerHTML = bootstrapImport;
        shadowRoot.appendChild(bootstrap);  
        const style = document.createElement("style");
        style.innerHTML = styleCSS;
        shadowRoot.appendChild(style);
        sheet.replaceSync(styleCSS);
        shadowRoot.adoptedStyleSheets.push(sheet);
    }
    async timelineMode() {
        view = container.dataset.view || "/timelineView.html";
        all = container.dataset.all || "/timelineAll.html";
        labelButtonViewAll = container.dataset.labelButtonViewAll || "Ver todas";
        disableButtonViewAll = container.dataset.disableButtonViewAll || "false";
        const response = await this.fetchBid(`${baseUrl}/timeline`, token);
        this.renderTimeline(response);
        if(response.length > 0){
            if(disableButtonViewAll === 'false'){
             const buttonViewAllContainer = document.createElement("div");
             buttonViewAllContainer.id = "container-view-all";
             buttonViewAllContainer.classList.add('flex', 'justify-content-center', 'items-center', 'w-100', 'text-center', 'mx-auto', 'my-2');
             shadowRoot.appendChild(buttonViewAllContainer);
             const buttonViewAll = document.createElement("a");
             buttonViewAll.id = "button-view-all";
             buttonViewAll.classList.add('mx-auto', 'my-2', 'btn', 'btn-primary');
             buttonViewAll.innerText = labelButtonViewAll;
             buttonViewAll.href = all;
             buttonViewAllContainer.appendChild(buttonViewAll);
            }
        }
    }
    async timelineAllMode() {
        view = container.dataset.view || "/timelineView.html";
        this.showSearchForm();
        paginationControls = document.createElement("div");
        paginationControls.id = "pagination-controls";
        shadowRoot.appendChild(paginationControls);
        currentPage = 1;
        window.onPageChange = this.onPageChange;
        this.onPageChange(currentPage);
    }
    async timelineViewMode() {
        const bidId = container.dataset.bidId || new URLSearchParams(window.location.search).get('bid');
        const response = await this.fetchBid(`${baseUrl}/timeline/${bidId}`, token);
        this.renderTimelineView(response);
    }
    // Inicialização
    init = async (call) => {
    shadowRoot = this.shadowRoot;
    container = document.getElementById('bids-plugin');

    // Proteção
    if (!container || !shadowRoot) return;

    // Limpa o conteúdo anterior
    shadowRoot.innerHTML = '';

    mode = container.dataset.mode;
    modeauth = container.dataset.modeAuth;
    disableButtonViewAll = container.dataset.disableButtonViewAll;
    disableReadMoreOnCard = container.dataset.disableReadMoreOnCard;
    baseUrl = container.dataset.url;
    appid = container.dataset.appId;
    token = container.dataset.token;
    stylesheet = container.dataset.stylesheet || "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css";

    this.injectStyle();
    this.createFlexContainer();

    // Timer de recarga contínua
    const restart = () => setTimeout(() => this.init(), mode === 'timeline.view' ? 1200000 : 600000);

    if (mode === 'timeline') {
        await this.timelineMode();
        restart();
    } else if (mode === 'timeline.all') {
        await this.timelineAllMode();
        restart();
    } else if (mode === 'timeline.view') {
        await this.timelineViewMode();
        restart();
    }

    console.log('BidsPlugin initialized');
    }
}
customElements.define('bids-plugin', BidsPlugin);
