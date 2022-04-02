
// наложение маски в компонентах с использованием vue  - Simple, Enter, Edit

if(BX && BX.Vue){
    BX.Vue.mutateComponent('BxmakerAuthuserphoneInput',
        {
            mounted: function () {
                if (this.name === 'PHONE') {
                    var that = this;
                    $(this.$refs.input).inputmask('mask',
                        {
                            'mask': '7(999) 999-9999',
                            'onKeyDown': function (event) {
                                setTimeout(function () {
                                    that.onInput(event);
                                }, 0);
                            }
                        }
                    );
                }
            },
            beforeDestroy: function () {
                if (this.name === 'PHONE') {
                    $(this.$refs.input).inputmask('unmaskedvalue');
                }
            },
        }
    );
}
