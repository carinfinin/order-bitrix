app.component('input-form', {
    props: {
        label: {
            type: String
        },
        type: {
            type: String,
            default: 'text'
        }
    },
    data() {
        return {
        }
    },
    template: `
        <label>{{ label }}</label>
        <input :type="type">
    `
})

