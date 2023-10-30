
// наложение маски в компонентах с использованием vue  - Simple, Enter, Edit

//  Для inputmask  --------------------
if(BX && BX.Vue){
    BX.Vue.mutateComponent('BXmakerAuthuserphoneInput',
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

//  Для imaskjs  --------------------
// https://imask.js.org/guide.html

if(BX && BX.Vue){
    BX.Vue.mutateComponent('BXmakerAuthuserphoneInput',
        {
            mounted: function () {
                var that = this;
                if (this.name === 'PHONE') {
                    that.setType('tel');
                    that.setPlaceholder('+7 (XXX) XXX-XX-XX');

                    //убираем дефолтную маску, часто встречающуюся
                    if(this.$refs.input.inputmask)
                    {
                        this.$refs.input.inputmask('unmaskedvalue');
                    }


                    this._mask =  IMask(this.$refs.input, {
                        mask: '+{7} (000) 000-00-00',
                    });
                    this._mask .on('accept', function (event) {
                        setTimeout(function () {
                            that.setValue(that._mask.value);
                        }, 0);
                    });
                }

            },
            beforeDestroy: function () {
                if (this.name === 'PHONE') {
                    this._mask.destroy();
                }
            },
        }
    );
}