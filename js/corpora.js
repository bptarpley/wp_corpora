const corporaTemplates = {
    searchTable: (params) => {
        return `
            <div id="corpora_search_table_${params['slug']}_status_div" class="${params['slug']} corpora-search-table-status-div pager-div">
                <span id="corpora_search_table_${params['slug']}_total_indicator" class="corpora-badge primary"></span>
            </div>
            <div class="corpora-search-table-search-div">
                <input type="text" id="corpora_search_table_${params['slug']}_search_box" class="corpora-search-table-search-box" placeholder="Search..." />
                <select id="corpora_search_table_${params['slug']}_field_select_box" class="corpora-search-table-dropdown" style="display: none;">
                    <option value="_all">All Fields</option>
                </select>
                <select id="corpora_search_table_${params['slug']}_search_select_box" class="corpora-search-table-dropdown" style="display: none;">
                </select>
            </div>
            <div class="corpora-search-table-wrapper">
                <table id="corpora_search_table_${params['slug']}" class="corpora-search-table">
                    <thead class="corpora-search-table-header">
                        <tr id="corpora_serach_table_${params['slug']}_header" class="corpora-search-table-header-row">
                            <th scope="col" class="corpora-search-table-action-header"></th>
                        </tr>
                    </thead>
                    <tbody id="corpora_serach_table_${params['slug']}_body" class="corpora-search-table-body"></tbody>
                </table>
            </div>
            <div id="corpora_search_table_${params['slug']}_footer_div" class="${params['slug']} pager-div">
                
            </div>
        `
    },
    searchHeader: (params) => {
        return `
            <th scope="col" class="corpora-search-table-header-cell" data-field="${params['name']}" data-type="${params['type']}" data-sortable="${params['sortable']}">${params['label']}</th>
        `
    },
    searchCell: (params) => {
        return `
            <td class="corpora-search-table-cell" data-type="${params['type']}">${params['value']}</td>
        `
    },
    detailFields: {
        text: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value}
                    </div>
                `;
        },
        large_text: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value}
                    </div>
                `;
        },
        keyword: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value}
                    </div>
                `;
        },
        html: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value}
                    </div>
                `;
        },
        number: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value}
                    </div>
                `;
        },
        decimal: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value}
                    </div>
                `;
        },
        boolean: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.value ? 'Yes' : 'No'}
                    </div>
                `;
        },
        date: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.corpora.date_string(params.value)}
                    </div>
                `;
        },
        timespan: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        ${params.corpora.timespan_string(params.value)}
                    </div>
                `;
        },
        file: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        <a href="${params.corpora.file_url(params.value.uri)}" download="${params.value.basename}">${params.value.basename}</a>
                    </div>
                `;
        },
        link: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        <a href="${params.value}" target="_blank">${params.field.label}</a>
                    </div>
                    <div class="col-10">&nbsp;</div>
                `;
        },
        'iiif-image': function(params) {
            return `
                    <div
                        id="${params.field.name}-iiif-viewer"
                        class="corpora-detail-field-value ${params.field.type}-value"
                        data-field-name="${params.field.name}"
                        data-iiif-identifier="${params.value}">
                    </div>
                `;
        },
        geo_point: function(params) {
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        <div id="${params.field.name}-map-viewer" class="w-100 geo_point-map" data-latitude="${params.value[1]}" data-longitude="${params.value[0]}" style="height: 300px;"></div>
                    </div>
                `;
        },
        cross_reference: function(params) {
            let intensity_indicator = '';
            if (params.field.has_intensity && params.value.hasOwnProperty('intensity')) intensity_indicator = ` (${params.value.intensity})`;
            return `
                    <div class="corpora-detail-field-value ${params.field.type}-value">
                        <a href="/${params.detail_page}/${params.value.content_type}/${params.value.id}" target="_blank">${params.value.label}</a>${intensity_indicator}
                    </div>
                `;
        }
    }
}

const fieldTypeSearchOptions = {
    keyword: ['q_', 't_', 'p_', 'f_', 'w_'],
    text: ['q_', 't_', 'p_', 'f_', 'w_'],
    cross_reference: ['q_', 't_', 'p_', 'f_', 'w_'],
    large_text: ['q_', 't_', 'p_', 'w_'],
    html: ['q_', 't_', 'p_', 'w_'],
    timespan: ['q_', 'r_']
}

const searchPrefixLabelMap = {
    't_': 'Term',
    'p_': 'Phrase',
    'f_': 'Exact',
    'w_': 'Wildcard',
    'q_': 'Text',
    'r_': 'Range'
}

const sortableFieldTypes = ['keyword', 'text', 'large_text',
    'number', 'decimal', 'boolean',
    'date', 'iiif-image', 'cross_reference']

class Corpora {
    constructor(host) {
        this.host = host;
    }

    make_request(path, type, params={}, callback, inject_host=true) {
        let url = path;
        if (inject_host) url = `${this.host}${path}`;

        let req = {
            type: type,
            url: url,
            dataType: 'json',
            crossDomain: true,
            data: params,
            success: callback,
            error: function() { callback(null) }
        };

        if (this.token) {
            let sender = this;
            req['beforeSend'] = function(xhr) { xhr.setRequestHeader("Authorization", `Token ${sender.token}`) };
        }

        return jQuery.ajax(req);
    }

    list_corpora(callback) {
        try {
            this.make_request(
                '/api/corpus/',
                'GET',
                {'page-size': 500},
                callback
            );
        } catch (error) {
            callback(null);
        }
    }

    file_url(uri) {
        return `${this.host}/file/uri/${uri.split('/').join('|')}/`;
    }

    date_string(timestamp, granularity='Day') {
        let date = new Date(timestamp)
        if (granularity === 'Day')
            return date.toISOString().split('T')[0]
        else if (granularity === 'Year')
            return date.toLocaleString('default', { year: 'numeric' })
        else if (granularity === 'Month')
            return date.toLocaleString('default', { month: 'long', year: 'numeric' })
        else if (granularity === 'Time')
            return date.toLocaleString('en-US', { timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone })
    }

    timespan_string(timespan) {
        let representation = ''
        let uncertain_prefix = 'Around'

        if (timespan.start) {
            representation += this.date_string(timespan.start, timespan.granularity)

            if (timespan.end) {
                representation += ` – ${this.date_string(timespan.end, timespan.granularity)}`
                uncertain_prefix = 'Between'
            }

            if (timespan.uncertain) {
                representation = `${uncertain_prefix} ${representation}`
            }
        }

        return representation
    }

    get_css_colors() {
        let docElStyle = getComputedStyle(document.documentElement)
        let primary = docElStyle.getPropertyValue('--corpora--primary--color')
        let secondary = docElStyle.getPropertyValue('--corpora--secondary--color')
        let foreground = docElStyle.getPropertyValue('--corpora--foreground--color')
        let background = docElStyle.getPropertyValue('--corpora--background--color')

        primary = primary ? primary : '#EF3E36'
        secondary = secondary ? secondary : '#091540'
        foreground = foreground ? foreground : '#f2f2f2'
        background = background ? background : '#636464'

        return {
            primary: primary,
            secondary: secondary,
            foreground: foreground,
            background: background
        }
    }

    set_css_colors(colors) {
        if (typeof colors === 'object' && colors !== null) {
            if ('primary' in colors)
                document.documentElement.style.setProperty('--corpora--primary--color', colors.primary)
            if ('secondary' in colors)
                document.documentElement.style.setProperty('--corpora--secondary--color', colors.secondary)
            if ('foreground' in colors)
                document.documentElement.style.setProperty('--corpora--foreground--color', colors.foreground)
            if ('background' in colors)
                document.documentElement.style.setProperty('--corpora--background--color', colors.background)
        }
    }
}


class Corpus {
    constructor(corpora, corpus_id) {
        this.corpora = corpora;
        this.id = corpus_id;
    }

    load(callback, include_views=false) {
        let sender = this;
        let params = {};

        if (include_views) { params['include-views'] = 'y'; }

        this.corpora.make_request(
            `/api/corpus/${sender.id}/`,
            'GET',
            params,
            function(data) {
                if (data.id && data.id === sender.id) {
                    sender.name = data.name;
                    sender.description = data.description;
                    sender.content_types = data.content_types;
                    callback(sender);
                }
            }
        )
    }
}


class CorpusSearch {
    constructor(corpora, corpus, search, detail_page, fixed_params={}) {
        this.corpora = corpora
        this.corpus = corpus
        this.detail_page = detail_page
        this.search = search
        this.fixed_params = fixed_params
        this.container = jQuery(`#corpora-search-${this.search.slug}`)
        this.content_type = this.search.content_type
        this.view_id = null

        if (this.content_type.includes('--')) {
            [this.content_type, this.view_id] = this.content_type.split('--')
        }

        if (this.content_type in this.corpus.content_types) {
            this.fields = []
            Object.keys(this.search.field_options).map(f_name => {
                if (this.search.field_options[f_name].show_in_searches) {
                    let field = null;
                    this.corpus.content_types[this.content_type].fields.forEach(f_spec => {
                        if (f_spec.name === f_name) field = f_spec
                    })

                    if (field) {
                        field = Object.assign(field, this.search.field_options[f_name])
                        this.fields.push(field)
                    }
                }
            })

            this.container.empty()

            if (this.search.display_name)
                this.container.append(`
                    <h4 class="corpora-associated-content-header">${this.search.name}</h4>
                `)

            this.container.append(corporaTemplates.searchTable({
                slug: this.search.slug
            }))

            this.table = jQuery(`#corpora_search_table_${this.search.slug}`)
            this.search_box = jQuery(`#corpora_search_table_${this.search.slug}_search_box`)
            this.field_selector = jQuery(`#corpora_search_table_${this.search.slug}_field_select_box`)
            this.search_selector = jQuery(`#corpora_search_table_${this.search.slug}_search_select_box`)
            this.status_div = jQuery(`#corpora_search_table_${this.search.slug}_status_div`)
            this.total_indicator = jQuery(`#corpora_search_table_${this.search.slug}_total_indicator`)
            this.table_header = jQuery(`#corpora_serach_table_${this.search.slug}_header`)
            this.table_body = jQuery(`#corpora_serach_table_${this.search.slug}_body`)

            // setup column headers
            this.fields.forEach(f => {
                if (f.show_in_searches) {
                    this.table_header.append(corporaTemplates.searchHeader({
                        name: f.name,
                        type: f.type,
                        label: f.label,
                        sortable: sortableFieldTypes.includes(f.type),
                    }))
                }

                this.field_selector.append(`
                    <option value="${f.name}${f.type === 'cross_reference' ? '.label' : ''}">${f.label}</option>
                `)
            })
            if (this.fields.length > 1) this.field_selector.show()

            // default search settings
            this.search_timer = null
            this.search_params = {
                'page-size': this.search.page_size,
                'page': 1,
            }

            // setup default sorting, if configured
            if (this.search.default_sort && this.search.default_sort.field) {
                this.search_params[`s_${this.search.default_sort.field}`] = this.search.default_sort.direction
                jQuery(`th.corpora-search-table-header-cell[data-field="${this.search.default_sort.field}"]`).addClass(this.search.default_sort.direction)
            }

            // --------------------//
            // RIG UP EVENTS       //
            // --------------------//
            let sender = this

            // search field selection change
            this.field_selector.change(function() {
                sender.search_selector.empty()

                let selected_field_name = sender.field_selector.val().split('.')[0]
                let selected_field = null

                sender.fields.forEach(f => {
                    if (f.name === selected_field_name) selected_field = f
                })

                if (selected_field && (selected_field.type in fieldTypeSearchOptions)) {
                    fieldTypeSearchOptions[selected_field.type].forEach(opt => {
                        sender.search_selector.append(`
                            <option value="${opt}">${searchPrefixLabelMap[opt]}</option>
                        `)
                    })
                    sender.search_selector.show()
                } else {
                    sender.search_selector.html(`<option value="q_">Default</option>`)
                    sender.search_selector.hide()
                }
            })

            // search box typing
            this.search_box.keyup(function() {
                clearTimeout(sender.search_timer);
                sender.search_timer = setTimeout(function() {
                    if (sender.search_box.val()) {
                        if (sender.field_selector.val() === '_all') {
                            sender.search_params['q'] = sender.search_box.val()
                        } else {
                            let prefix = sender.search_selector.val()
                            let field = sender.field_selector.val()

                            sender.search_params[`${prefix}${field}`] = sender.search_box.val()
                        }
                    }

                    delete sender.search_params['page']

                    sender.load_page()
                }, 1000);
            })

            // column header click for sorting
            jQuery('.corpora-search-table-header-cell[data-sortable=true]').click(function() {
                let cell = jQuery(this)
                let param = `s_${cell.data('field')}`
                if (cell.data('type') === 'cross_reference') param += '.label'

                if (!(param in sender.search_params)) {
                    sender.search_params[param] = 'asc'
                    cell.addClass('asc')
                }
                else if (sender.search_params[param] === 'asc') {
                    sender.search_params[param] = 'desc'
                    cell.removeClass('asc')
                    cell.addClass('desc')
                }
                else {
                    delete sender.search_params[param]
                    cell.removeClass('desc')
                }

                sender.load_page()
            })

            if (this.view_id) this.search_params['content_view'] = this.view_id
            this.load_page()
        }

    }

    load_page() {
        let endpoint = `/api/corpus/${this.corpus.id}/${this.content_type}/`
        let sender = this

        // populate search criteria badges
        jQuery('.corpora-search-criteria').remove()
        Object.keys(sender.search_params).forEach(param => {
            let label = ''
            if (param.length > 2 && ['t', 'p', 'f', 'w', 'q'].includes(param[0]) && param[1] === '_') {
                let prefix = param[0] + '_'
                let field_name = param.replace(prefix, '').split('.')[0]
                let field_label = null

                sender.fields.forEach(f => {
                    if (f.name === field_name) field_label = f.label
                })

                label = `${searchPrefixLabelMap[prefix]} searching ${field_label} "${sender.search_params[param]}"`
            } else if (param === 'q') label = `Searching "${sender.search_params[param]}"`

            if (label) {
                sender.status_div.append(`
                    <span class="corpora-badge secondary corpora-search-criteria">
                        ${label} <span class="dashicons dashicons-dismiss corpora-remove-criteria" data-param="${param}"></span>
                    </span>
                `)
            }
        })

        // click event for removing search criteria
        jQuery('.corpora-remove-criteria').click(function() {
            let criteria = jQuery(this)
            delete sender.search_params[criteria.data('param')]
            sender.load_page()
        })

        // actually perform search and load results
        this.corpora.make_request(
            endpoint,
            'GET',
            Object.assign(sender.search_params, sender.fixed_params),
            function(data) {
                if (data.records && data.meta) {
                    sender.table_body.empty()

                    if (data.records.length) {
                        sender.total_indicator.show()
                        sender.total_indicator.html(`Total: ${data.meta.total}`)

                        // set up pagination
                        jQuery(`.${sender.search.slug}.corpora-search-table-page-size-span`).remove()
                        jQuery(`.${sender.search.slug}.corpora-search-table-pagination-span`).remove()
                        if (data.meta.num_pages > 1) {

                            // page size selector
                            jQuery(`#corpora_search_table_${sender.search.slug}_footer_div`).append(`
                                <span class="${sender.search.slug} corpora-search-table-page-size-span">
                                    <label for="corpora_search_table_${sender.search.slug}_page_size_select_box">Page size: </label>
                                    <select id="corpora_search_table_${sender.search.slug}_page_size_select_box" class="corpora-search-table-search-box corpora-search-table-page-size-dropdown">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </span>
                            `)
                            let page_size_box = jQuery(`#corpora_search_table_${sender.search.slug}_page_size_select_box`)
                            page_size_box.val(sender.search_params['page-size'])
                            page_size_box.change(function() {
                                sender.search_params['page-size'] = jQuery(this).val()
                                sender.search_params['page'] = 1
                                sender.load_page()
                                sender.total_indicator[0].scrollIntoView({behavior: 'smooth'})
                            })

                            // pagers
                            jQuery(`.${sender.search.slug}.pager-div`).append(`
                                <span class="${sender.search.slug} corpora-search-table-pagination-span">
                                    <span class="dashicons dashicons-arrow-left-alt2 ${sender.search.slug} corpora-pager${data.meta.page > 1 ? ' enabled': ''}" data-direction="prev"></span>
                                    <span class="corpora-badge primary">Page ${data.meta.page} of ${data.meta.num_pages}</span>
                                    <span class="dashicons dashicons-arrow-right-alt2 ${sender.search.slug} corpora-pager${data.meta.page < data.meta.num_pages ? ' enabled': ''}" data-direction="next"></span>
                                </span>
                            `)
                            jQuery(`.${sender.search.slug}.corpora-pager.enabled`).click(function() {
                                let pager = jQuery(this)
                                if (pager.data('direction') === 'prev' && data.meta.page > 1) {
                                    sender.search_params['page'] = data.meta.page - 1
                                } else if (pager.data('direction') === 'next' && data.meta.page < data.meta.num_pages)
                                    sender.search_params['page'] = data.meta.page + 1

                                sender.load_page()
                                sender.total_indicator[0].scrollIntoView({behavior: 'smooth'})
                            })
                        }

                        data.records.forEach(row => {
                            let row_html = `
                                <tr class="corpora-search-table-row">
                                    <td class="corpora-search-table-cell">
                                        <a href="/${sender.detail_page}/${sender.content_type}/${row.id}" target="_blank"><span class="dashicons dashicons-external"></span></a>
                                    </td>`

                            sender.fields.forEach(f => {
                                let field_value = row[f.name] ?? ''
                                if (f.multiple && field_value) {
                                    field_value = field_value.map(val => sender.convert_field_value(val, f))
                                    field_value = field_value.join(', ')
                                } else {
                                    field_value = sender.convert_field_value(field_value, f)
                                }

                                row_html += corporaTemplates.searchCell({
                                    type: f.type,
                                    value: field_value
                                })
                            })
                            row_html += `</tr>`
                            sender.table_body.append(row_html)
                        })
                    } else {
                        sender.total_indicator.hide()
                        sender.table_body.append(`
                            <tr>
                                <td colspan="${sender.fields.length + 1}">
                                    No results
                                </td>
                            </tr>
                        `)
                    }
                }
            }
        )
    }

    convert_field_value(val, field) {
        let field_value = val;
        if (val && field.type === 'cross_reference') {
            field_value = `
                <a href="/${this.detail_page}/${field.cross_reference_type}/${field_value.id}" target="_blank">${field_value.label}</a>
            `;
        } else if (val && field.type === 'iiif-image') {
            field_value = `
                <img src="${val}/full/,100/0/default.jpg" />
            `
        } else if (val && field.type === 'date') {
            field_value = this.corpora.date_string(val)
        } else if (val && field.type === 'timespan') {
            field_value = this.corpora.timespan_string(val)
        }

        return field_value;
    }
}


class CorporaAggregationMap extends HTMLElement {
    constructor() {
        super()
        this.attachShadow({mode: 'open'})
        this.config = {}
        this.theatresAdded = new Set()
        this.el = document.createElement('div')
        this.map = null
        this.theatreLayer = L.layerGroup()

        this.configured = true
        this.requiredConfigAttributes = [
            'corpora_host',
            'corpora_corpus',
            'corpora_content_type',
            'corpora_geopoint_field',
            'corpora_location_content_type',
            'corpora_location_id_field',
            'detail_page_url',
            'plugin_dir_url',
            'location_icon_url',
            'icon_background_color'
        ]
    }

    connectedCallback() {
        for (let attrIndex = 0; attrIndex < this.attributes.length; attrIndex++) {this.config[this.attributes[attrIndex].name] = this.attributes[attrIndex].value}
        this.requiredConfigAttributes.forEach(attr => {
            if (!this.config.hasOwnProperty(attr)) {
                console.log(`CorporaAggMap requires the following missing attribute to be set: ${attr}`)
                this.configured = false
            }
        })

        if (this.configured) {
            let idSuffix = 0
            while (document.getElementById(`corpora-map-${idSuffix}`)) idSuffix += 1
            this.el.setAttribute('id', `corpora-map-${idSuffix}`)
            this.el.style.height = 'height' in this.config ? this.config.height : '400px'
            this.el.style.width = 'width' in this.config ? this.config.width : '80%'
            this.shadowRoot.innerHTML = `
                <link rel="stylesheet" href="${this.config.plugin_dir_url}/js/leaflet/leaflet.css">
                <style>
                    .corpora-aggmap-marker {
                        background-color: ${this.config.icon_background_color};
                        border-top-left-radius: 25px;
                        border-bottom-left-radius: 25px;
                        display: flex;
                    }
                    .corpora-aggmap-marker-circle {
                        height: 50px;
                        min-width: 50px;
                        border-radius: 50%;
                        background-image: url('${this.config.location_icon_url}');
                        background-repeat: no-repeat;
                        background-color: ${this.config.icon_background_color};
                    }
                    .corpora-aggmap-marker-label {
                        padding: 5px;
                    }
                </style>
            `
            this.shadowRoot.appendChild(this.el)

            let basemap = 'basemap_uri' in this.config ? this.config.basemap_uri : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
            this.map = L.map(this.el)
            L.tileLayer(basemap, {
                maxZoom: 19,
                noWrap: true
            }).addTo(this.map)

            if (this.config.top_left_bounds && this.config.bottom_right_bounds) {
                this.config.top_left_bounds = this.config.top_left_bounds.split(',').map(coord => parseFloat(coord))
                this.config.bottom_right_bounds = this.config.bottom_right_bounds.split(',').map(coord => parseFloat(coord))
                this.map.fitBounds([
                    this.config.top_left_bounds,
                    this.config.bottom_right_bounds
                ], {padding: [20, 20]})
                this.drawClusters()
            } else {
                let boundsQuery = `${this.config.corpora_host}/api/corpus/${this.config.corpora_corpus}/${this.config.corpora_content_type}/`
                boundsQuery += `?a_geobounds_area=${this.config.corpora_geopoint_field}&page-size=0&es_debug=y`
                fetch(boundsQuery)
                    .then(res => res.json())
                    .then(data => {
                        if (this.hasProp(data, 'meta.aggregations.area')) {
                            let topLeft = data.meta.aggregations.area.top_left
                            let bottomRight = data.meta.aggregations.area.bottom_right

                            this.map.fitBounds([
                                [topLeft.lat, topLeft.lon],
                                [bottomRight.lat, bottomRight.lon]
                            ], {padding: [20, 20]})

                            this.drawClusters()
                        }
                    })
            }
        }
    }

    drawClusters() {
        this.markerCluster = L.markerClusterGroup({
            iconCreateFunction: cluster => {
                let size = cluster.getAllChildMarkers().map(marker => marker.performances).reduce((a, b) => a + b)
                return L.divIcon({
                    className: 'corpora-aggmap-cluster',
                    html: `
                        <svg height="50" width="50">
                          <circle
                            class="corpora-aggmap-cluster-circle"
                            cx="25"
                            cy="25"
                            r="24"
                            fill="${this.config.icon_background_color}"
                            stroke="white"
                            stroke-width="1" />
                          <text
                            x="${size > 9 ? 17 : 22}"
                            y="25"
                            stroke="black"
                            stroke-width="1"
                            dy=".3em">
                            ${size.toLocaleString()}  
                          </text>
                        </svg>
                    `
                })
            },
            maxClusterRadius: 60,
            zoomToBoundsOnClick: false,
            spiderfyOnMaxZoom: false
        })
        this.markerCluster.addEventListener('clusterclick', (e) => {
            this.map.flyToBounds(e.layer.getBounds(), {duration: 1, padding: [80, 80]})
        })

        let clusterQuery = `${this.config.corpora_host}/api/corpus/${this.config.corpora_corpus}/${this.config.corpora_content_type}/`
        clusterQuery += `?a_geotile_clusters=${this.config.corpora_geopoint_field}__19&page-size=0`
        fetch(clusterQuery)
            .then(res => res.json())
            .then(data => {
                if (this.hasProp(data, 'meta.aggregations.clusters')) {
                    let clusters = data.meta.aggregations.clusters
                    Object.keys(clusters).forEach(geoTile => {
                        let [zoom, x, y] = geoTile.split('/')
                        let latLong = this.geoTileToLatLong(x, y, zoom)

                        let marker = new L.Marker(
                            latLong,
                            {
                                icon: new L.divIcon({
                                    className: 'corpora-aggmap-cluster',
                                    html: `
                                        <svg height="50" width="50">
                                          <circle
                                            class="corpora-aggmap-cluster-circle"
                                            cx="25"
                                            cy="25"
                                            r="24"
                                            fill="${this.config.icon_background_color}"
                                            stroke="white"
                                            stroke-width="1" />
                                          <text
                                            x="${clusters[geoTile] > 9 ? 17 : 22}"
                                            y="25"
                                            stroke="black"
                                            stroke-width="1"
                                            dy=".3em">
                                            ${clusters[geoTile].toLocaleString()}  
                                          </text>
                                        </svg>
                                    `
                                })
                            }
                        )
                        marker.performances = clusters[geoTile]
                        marker.on('click', (e) => {
                            this.map.flyTo(e.latlng, 13, {duration: 1})
                        })
                        this.markerCluster.addLayer(marker)
                    })
                    this.map.addLayer(this.markerCluster)

                    this.map.addEventListener('zoomend', (e) => this.viewChanged())
                    this.map.addEventListener('moveend', (e) => this.viewChanged())
                }
            })
        // end fetch
    }

    viewChanged() {
        if (this.map.getZoom() > 12) {
            this.map.removeLayer(this.markerCluster)
            this.map.addLayer(this.theatreLayer)
            this.drawTheatres()
        } else {
            this.map.removeLayer(this.theatreLayer)
            this.map.addLayer(this.markerCluster)
        }
    }

    drawTheatres() {
        let bounds = this.map.getBounds()
        let theatreRange = `${bounds._southWest.lng},${bounds._northEast.lat}to${bounds._northEast.lng},${bounds._southWest.lat}`
        let theatresQuery = `${this.config.corpora_host}/api/corpus/${this.config.corpora_corpus}/${this.config.corpora_content_type}/`
        theatresQuery += `?a_terms_theatres=theatre.id&r_${this.config.corpora_geopoint_field}=${theatreRange}&page-size=0&es_debug=y`

        fetch(theatresQuery)
            .then(res => res.json())
            .then(data => {
                if (this.hasProp(data, 'meta.aggregations.theatres')) {
                    let theatresInRange = data.meta.aggregations.theatres

                    Object.keys(theatresInRange).forEach(theatreID => {
                        if (!this.theatresAdded.has(theatreID)) {
                            let theatreQuery = `${this.config.corpora_host}/api/corpus/${this.config.corpora_corpus}/${this.config.corpora_location_content_type}/${theatreID}/`
                            fetch(theatreQuery)
                                .then(res => res.json())
                                .then(theatreData => {
                                    let marker = new L.Marker(
                                        [theatreData.coordinates[1], theatreData.coordinates[0]],
                                        {
                                            icon: new L.DivIcon({
                                                className: 'corpora-aggmap-marker',
                                                iconSize: [150, 50],
                                                iconAnchor: [0, 0],
                                                html: `
                                                <div class="corpora-aggmap-marker-circle">
                                                </div>
                                                <div class="corpora-aggmap-marker-label">
                                                  ${theatreData.label} (${theatresInRange[theatreID].toLocaleString()})
                                                </div>
                                            `
                                            })
                                        }
                                    )
                                    marker.detail_url = `${this.config.detail_page_url}/${this.config.corpora_location_content_type}/${theatreData.id}`
                                    marker.on('click', (e) => {
                                        window.open(e.target.detail_url, '_blank')
                                    })
                                    this.theatreLayer.addLayer(marker)
                                    this.theatresAdded.add(theatreID)
                                })
                            // end fetch
                        }
                    })
                }
            })
        //end fetch
    }

    geoTileToLatLong(x, y, z) {
        // Calculate longitude
        const lon = (2 * Math.PI * x / Math.pow(2, z) - Math.PI)

        // Calculate latitude
        const lat = (2 * Math.atan(Math.exp(Math.PI - (2 * Math.PI * y / Math.pow(2, z)))) - Math.PI / 2)

        // Convert radians to degrees
        return [
            lat * 180 / Math.PI,
            lon * 180 / Math.PI
        ]
    }

    hasProp(obj, path) {
        return path.split(".").every(function(x) {
            if(typeof obj != "object" || obj === null || ! x in obj)
                return false
            obj = obj[x]
            return true
        })
    }
}
customElements.define('corpora-aggregation-map', CorporaAggregationMap)


class CorpusContent {
    constructor(corpora, corpus, content_type, content_id, detail_page, plugin_url, content_type_templates, highlight) {
        this.corpora = corpora
        this.corpus = corpus
        this.content_type_name = content_type
        this.content_type = null
        this.content_id = content_id
        this.detail_page = detail_page
        this.plugin_url = plugin_url
        this.content_type_templates = content_type_templates
        this.use_template = false
        this.highlight = highlight
        this.title_label = jQuery('#corpora-detail-content-label')
        this.container = jQuery('#corpora-detail-content-div')
        this.content = null

        if (this.content_type_name in this.corpus.content_types) {

            // if a content type template was configured for this content type, render it
            if (this.content_type_templates &&
                    (this.content_type_name in this.content_type_templates) &&
                    this.content_type_templates[this.content_type_name].template) {

                this.use_template = true

            // otherwise, build the default representation of this content
            } else {
                this.content_type = this.corpus.content_types[this.content_type_name]

                for (let x = 0; x < this.content_type.fields.length; x++) {
                    let field = this.content_type.fields[x]
                    let hidden = false

                    if (this.content_type_templates &&
                            (this.content_type_name in this.content_type_templates) &&
                            this.content_type_templates[this.content_type_name].hidden_fields.includes(field.name))
                        hidden = true

                    if (field.type !== 'embedded' && !hidden) {
                        this.container.append(`
                        <div id="${field.name}-content" class="corpora-detail-field-div">
                            <div class="corpora-detail-field-label">
                                <strong>${field.label}</strong>
                            </div>
                        </div>
                    `);
                    }
                }
            }

            let sender = this
            let request_params = {}
            if (this.use_template) request_params['only'] = 'label'

            this.corpora.make_request(
                `/api/corpus/${this.corpus.id}/${this.content_type_name}/${this.content_id}/`,
                'GET',
                request_params,
                function(data) {
                    sender.content = data
                    sender.title_label.html(sender.content.label)

                    if (sender.use_template) {
                        let template_request_url = `${sender.corpora.host}/api/corpus/${sender.corpus.id}/${sender.content_type_name}/${sender.content_id}/?render_template=${sender.content_type_templates[sender.content_type_name].template}`
                        sender.container.load(template_request_url, sender.show_associated())

                    } else {
                        sender.inject_content()
                        sender.show_associated()
                    }
                }
            )
        }
    }


    inject_content() {
        let has_geo_field = false
        let has_iiif_field = false

        for (let f_index = 0; f_index < this.content_type.fields.length; f_index++) {
            let field = this.content_type.fields[f_index];
            let field_div = jQuery(`#${field.name}-content`);

            if (field_div.length && field.type !== 'embedded') {
                // ensure this field has a valid value set
                let has_value = true
                if (this.content === null) has_value = false
                else if ((this.content[field.name] ?? '') === '') has_value = false
                else if (field.multiple && this.content[field.name].length === 0) has_value = false

                if (has_value) {
                    let template_params = {
                        field: field,
                        value: this.content[field.name] ?? "",
                        corpora: this.corpora,
                        detail_page: this.detail_page
                    }

                    if (field.type === 'link') field_div.html('')
                    else if (field.type === 'iiif-image') has_iiif_field = true
                    else if (field.type === 'geo_point') has_geo_field = true

                    if (field.multiple) {
                        this.content[field.name].forEach(function (value, index) {
                            let multi_field = Object.assign({}, field)
                            multi_field.name = `${multi_field.name}-${index}`
                            template_params.field = multi_field
                            template_params.value = value
                            field_div.append(corporaTemplates.detailFields[field.type](template_params))
                        });
                    } else {
                        field_div.append(corporaTemplates.detailFields[field.type](template_params))
                    }
                } else if (this.content_type_templates &&
                    (this.content_type_name in this.content_type_templates) &&
                    this.content_type_templates[this.content_type_name].show_empty) {
                    field_div.append(`<div class="corpora-detail-field-value ${field.type}-value"><span class="badge badge-secondary">Not set</span></div>`)
                } else {
                    field_div.css("display", "none")
                }
            }
        }

        if (has_geo_field) {
            jQuery('.geo_point-map').each(function() {
                let map_div = jQuery(this)
                let coords = [parseFloat(map_div.data('latitude')), parseFloat(map_div.data('longitude'))]
                let map = L.map(map_div[0].id).setView(coords, 13)
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map)
                L.marker(coords).addTo(map)
            })
        }

        if (has_iiif_field) {
            let sender = this
            jQuery('.iiif-image-value').each(function() {
                let iiif_div = jQuery(this)
                OpenSeadragon({
                    id:                 iiif_div[0].id,
                    prefixUrl:          `${sender.plugin_url}js/openseadragon/images/`,
                    preserveViewport:   false,
                    visibilityRatio:    1,
                    minZoomLevel:       .25,
                    maxZoomLevel:       5,
                    defaultZoomLevel:   0,
                    showRotationControl: true,
                    tileSources:   [iiif_div.data('iiif-identifier')],
                })
            })
        }
    }


    show_associated() {
        let proceed = true
        let referencing_cts = {}

        // check settings to make sure we should show associated content
        if (this.content_type_templates &&
            (this.content_type_name in this.content_type_templates) &&
            this.content_type_templates[this.content_type_name].show_associated === false)
            proceed = false

        if (proceed) {
            // check content type schema to see if any CT's reference this one
            for (let ct_name in this.corpus.content_types) {
                if (ct_name !== this.content_type_name) {
                    let candidate_ct = this.corpus.content_types[ct_name]

                    candidate_ct.fields.forEach(f => {
                        if (f.type === 'cross_reference' && f.cross_reference_type === this.content_type_name) {
                            if (!(ct_name in referencing_cts)) referencing_cts[ct_name] = []
                            referencing_cts[ct_name].push(f.name)
                        }
                    })
                }
            }

            if (Object.keys(referencing_cts).length) {
                this.container.append(`<h2>Associated Content</h2>`)
                Object.keys(referencing_cts).forEach(ct_name => {
                    let ct = this.corpus.content_types[ct_name]
                    let ct_prefs = this.content_type_templates[ct_name] ?? { hidden_fields: [] }
                    let search_name = `associated-${ct_name}`
                    let search_params = {
                        content_type: ct_name,
                        name: `${ct.plural_name}`,
                        page_size: 5,
                        display_name: true,
                        slug: search_name,
                        field_options: {}
                    }
                    let fixed_params = {}

                    // iterate over fields, respecting detail preferences re:field visibility
                    ct.fields.forEach(f => {
                        search_params.field_options[f.name] = {
                            show_in_searches: !ct_prefs.hidden_fields.includes(f.name)
                        }
                    })

                    // specify constraints for only showing associated content
                    referencing_cts[ct_name].forEach(field_name => {
                        fixed_params[`f_${field_name}.id`] = this.content_id
                    })
                    if (referencing_cts[ct_name].length > 1) fixed_params['operator'] = 'or'

                    // add associated content search
                    this.container.append(`<div id="corpora-search-${search_name}" class="corpora-search-div">`);
                    let search = new CorpusSearch(
                        this.corpora,
                        this.corpus,
                        search_params,
                        this.detail_page,
                        fixed_params
                    )
                })
            }
        }


    }
}
