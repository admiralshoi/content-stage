



class ModalHandler {
    modalTemplateBaseDirectory = serverHost + "includes/html/modals/";
    modalTemplates = {
        integrationSelection: {
            file: 'integrationSelection.html',
            keys: ["instagram", "facebook"]
        }
    };

    constructor(template) {
        if(!Object.keys(this.modalTemplates).includes(template)) {
            this.init = false;
            return;
        }

        this.reset()
        this.templateName = template;
        this.templateSelected = this.modalTemplates[template];
        this.templateFile = this.modalTemplateBaseDirectory + this.templateSelected.file;
    }
    reset () {
        this.templateName = null;
        this.templateSelected = null;
        this.templateFile = null;
        this.templateRaw = null;
        this.template = null;
        this.init = true;
        this.error = null;
        this.data = null;
        this.templateId = null;
        this.modal = null;
        this.isOpen = false;
        this.isBuild = false;
    }
    construct(data, bind = true) {
        if(!this.init) return false;
        for(let key of this.templateSelected.keys) {
            if(!Object.keys(data).includes(key)) {
                this.error = `Construction failed for template ${this.templateName}. Missing data key: ${key}`
                return false;
            }
            if(empty(this.data)) this.data = {}
            this.data[key] = data[key]
        }
        if(bind) this.bindEvents()
    }
    async build() {
        if(!this.init) return false;
        if(!empty(this.error)) {
            console.error(`Build error: ${this.error}`)
            return null;
        }

        await $.get(this.templateFile)
            .then(responseText => {
                this.templateRaw = responseText
            })
            .catch(error => {
                let code = error.status, errorText = error.statusText;
                console.error(`Build error while fetching template:: (${code}) ${errorText}`)
            })

        if(empty(this.templateRaw)) return null;
        this.templateId = $(this.templateRaw).attr("id");
        this.template = (Handlebars.compile(this.templateRaw))(this.data);

        $("body").append(this.template)
        this.template = $("body").find(`#${this.templateId}`).first()
        this.modal = this.template.modal()

        this.isBuild = true;
    }
    open() {
        if(!this.isBuild) return false;
        if(this.isOpen) return true;
        this.modal.modal('show')
        this.isOpen = true;
    }
    close() {
        if(!this.isBuild) return false;
        if(!this.isOpen) return true;
        this.modal.modal('hide')
        this.isOpen = false;
    }
    toggle() {
        if(!this.isBuild) return false;
        this.modal.modal('toggle')
        this.isOpen = !this.isOpen;
    }
    dispose() {
        if(!this.isBuild) return false;
        this.close()
        this.modal.modal('dispose')
        this.template.remove()
        this.reset()
    }
    bindEvents(opt = {}) {
        if(!this.isBuild) return false;
        let buttons = this.template.find("button")
        if(!buttons.length) return;
        let cl = this;

        buttons.each(function () {
            let fn = $(this).attr("data-run");
            if(empty(fn) || (!(fn in window) && !Object.keys(opt).includes(fn))) return;


            $(this).on("click", function () {
                if(Object.keys(opt).includes(fn)) opt[fn]($(this), cl)
                else window[fn]($(this), cl)
            })
        })
    }


}


