var config = {
    paths: {
        intlTelInput: 'Mitto_Login/js/lib/intlTelInput-jquery.min',
        intlTelInputUtils: 'Mitto_Login/js/lib/intlTelInput-utils',
    },
    shim: {
        intlTelInput: {deps: ['jquery', 'intlTelInputUtils']},
    }
};
