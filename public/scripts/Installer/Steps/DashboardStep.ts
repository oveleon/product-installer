import StepComponent from "../Components/StepComponent";
import ProductComponent from "../Components/ProductComponent";
import DropMenuComponent from "../Components/DropMenuComponent";
import PopupComponent, {PopupType} from "../Components/PopupComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";
import SetupStep from "./SetupStep";
import Installer from "../Installer";

/**
 * An overview of registered products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class DashboardStep extends StepComponent
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <div class="head-actions">
                <h2>${i18n('dashboard.headline')}</h2>
            </div>
            <div class="products inherit"></div>
            <div class="actions">
                <button data-close>${i18n('actions.close')}</button>
                <button class="primary" data-next>${i18n('dashboard.actions.register')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected mount(): void
    {
        // Lock step and protect for deletion
        this.locked = true
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Reset / remove steps from modal
        this.modal.removeSteps()

        // Skip dashboard when we have active redirects
        if(State.get('isRedirect'))
        {
            this.modal.next()
            return
        }

        // Show loader
        this.modal.loader(true, i18n('dashboard.loading'))

        // Create settings menu
        const settings = new DropMenuComponent([
            {
                label: i18n('dashboard.toggle.fullscreen'),
                value: () => {
                    Installer.modal.template.classList.toggle('pi--fs')
                }
            },
            {
                separator: true,
                label: i18n('dashboard.toggle.darkLight'),
                value: () => {
                    Installer.setColorScheme(Installer.modal.template.dataset.colorScheme === 'dark' ? 'light' : 'dark')
                }
            },
        ])

        settings.appendTo(<HTMLDivElement> this.element('.head-actions'))
        settings.addClass('gear')


        // Check license
        call('/contao/api/license_connector/products').then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            this.createProductList(response)

        }).catch((e: Error) => super.error(e))
    }

    /**
     * Create product list or empty message
     *
     * @param response
     * @protected
     */
    protected createProductList(response): void
    {
        const container = this.element('.products')
        container.innerHTML = '';

        let hasProducts = false

        for (const connector of response)
        {
            if(connector?.error)
            {
                super.error(connector)
                continue
            }

            if(!connector?.products.length)
            {
                continue
            }

            hasProducts = true

            // Collect products to sort them by removed flag
            const products = [];

            for(const productConfig of connector.products)
            {
                // Create product
                const product = new ProductComponent(productConfig)

                // Create menu options
                const menuOptions = []

                // Option: Setup product
                if(!product.isRemoved())
                {
                    menuOptions.push({
                        label: i18n('product.setup'),
                        value: () => {
                            // Create setup step
                            const setupStep = new SetupStep(productConfig.hash)

                            // Add setup step
                            this.modal.addSteps(setupStep)

                            // Goto setup
                            this.modal.open(this.modal.getStepIndex(setupStep))
                        },
                        highlight: !product.get('setup')
                    })
                }

                // Option: Product update option
                if(product.hasNewVersion() && !product.isRemoved())
                {
                    menuOptions.push({
                        label: i18n('product.update'),
                        value: () => console.log('Product updates are currently not supported, for an update you can do the product registration again.'),
                        highlight: true
                    })
                }

                // Option: Product info
                menuOptions.push({
                    label: i18n('product.info'),
                    value: () => {
                        const popup = new PopupComponent({
                            type: PopupType.TABLE,
                            appendTo: this.modal.insideContainer,
                            title: i18n('product.info'),
                            content: {
                                [i18n('product.label.shop')]: connector.connector.title,
                                [i18n('product.label.title')]: productConfig.title,
                                [i18n('product.label.description')]: productConfig.description,
                                [i18n('product.label.version')]: productConfig.version,
                                [i18n('product.label.latestVersion')]: productConfig.latestVersion ? productConfig.latestVersion : '-',
                                [i18n('product.label.registered')]: productConfig.registered && !productConfig.remove ? i18n('global.yes') : i18n('global.no'),
                                [i18n('product.label.registeredDate')]: productConfig?.license?.registered ? (new Date(productConfig.license.registered * 1000).toLocaleDateString()) : '-'
                            },
                            closeable: true
                        })

                        popup.show()
                    }
                })

                // Option: Remove products from list
                if(product.isRemoved())
                {
                    menuOptions.push({
                        label: i18n('product.remove'),
                        value: () => {
                            // Show loader
                            this.modal.loader(true, i18n('product.loading.remove'))

                            // Check license
                            call('/contao/api/license_connector/lock/remove', {
                                hash: product.get('hash')
                            }).then(() => {
                                // Hide loader
                                this.modal.loader(false)

                                // Remove product from list
                                product.template.remove()

                            }).catch((e: Error) => super.error(e))
                        }
                    })
                }

                // Set product menu
                product.setMenu(new DropMenuComponent(menuOptions))

                // Push product to sort by removed-flag
                products.push({
                    remove: product.get('remove'),
                    product
                })
            }

            // Sort products
            products.sort((a, b) => b.remove - a.remove);

            // Render products
            for (const product of products)
            {
                product.product.appendTo(container)
            }
        }

        if(!hasProducts)
        {
            container.innerHTML = `<div class="graphic no-products"><span>${i18n('dashboard.noProducts')}</span></div>`
            return
        }
    }
}
