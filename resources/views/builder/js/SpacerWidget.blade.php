<script>
    // Spacer Widget — vertical spacing block
    class SpacerWidget extends Widget {
        getHtmlId() {
            return "SpacerWidget";
        }

        init() {
            // default button html — using inline SVG icon (no external font dependency)
            this.setButtonHtml(`
                <div class="_1content widget-text">
                    <div class="panel__body woo-panel__body" title="{{ trans('builder.widget.spacer') }}">
                        <div class="image-drag">
                            <div class="ng-binding" style="display:flex;align-items:center;justify-content:center;height:40px;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="3" x2="12" y2="21"></line>
                                    <polyline points="8 7 12 3 16 7"></polyline>
                                    <polyline points="8 17 12 21 16 17"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="body__title">{{ trans('builder.widget.spacer') }}</div>
                    </div>
                </div>
            `);

            // default content html — empty spacing div
            this.setContentHtml(`
                <div id="`+ this.id + `" builder-element="CellContainerElement" style="padding:0;">
                    <div style="height:40px;width:100%;"></div>
                </div>
            `);

            // default dragging html
            this.setDraggingHtml(this.getButtonHtml());
        }

        getElement() {
            return currentEditor.getIframeContent().find('#' + this.id);
        }

        drop() {
        }
    }
</script>