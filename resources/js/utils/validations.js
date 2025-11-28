// resources/js/utils/validations.js

export const validationRules = {
    required: (value) => !!value || 'Este campo es obligatorio',
    email: (value) => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(value) || 'Ingrese un correo electrónico válido';
    },
    minLength: (min) => (value) => 
        (value && value.length >= min) || `Debe tener al menos ${min} caracteres`,
    maxLength: (max) => (value) => 
        (value && value.length <= max) || `No puede exceder ${max} caracteres`,
    numeric: (value) => {
        const pattern = /^\d+$/;
        return pattern.test(value) || 'Solo se permiten números';
    },
    decimal: (value) => {
        const pattern = /^\d+(\.\d{1,2})?$/;
        return pattern.test(value) || 'Ingrese un número válido (máximo 2 decimales)';
    },
    positive: (value) => 
        (parseFloat(value) > 0) || 'El valor debe ser mayor a 0',
    phone: (value) => {
        const pattern = /^[0-9]{7,10}$/;
        return pattern.test(value) || 'Ingrese un número de teléfono válido';
    }
};

export const validateForm = (formData, rules) => {
    const errors = {};
    
    Object.keys(rules).forEach(field => {
        const fieldRules = rules[field];
        const value = formData[field];
        
        for (const rule of fieldRules) {
            const result = rule(value);
            if (result !== true) {
                errors[field] = result;
                break; // Solo mostrar el primer error
            }
        }
    });
    
    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
};

export const formatErrorMessage = (error) => {
    // Convertir errores del servidor Laravel a español
    const errorMessages = {
        'The field is required.': 'Este campo es obligatorio.',
        'The field must be a string.': 'Este campo debe ser texto.',
        'The field must be a number.': 'Este campo debe ser un número.',
        'The field must be an integer.': 'Este campo debe ser un número entero.',
        'The field format is invalid.': 'El formato de este campo es inválido.',
        'The field must be at least': 'Este campo debe tener al menos',
        'The field may not be greater than': 'Este campo no puede ser mayor a',
        'The selected field is invalid.': 'El valor seleccionado es inválido.',
        'The field must be accepted.': 'Debe aceptar este campo.',
        'The field confirmation does not match.': 'La confirmación no coincide.',
        'The field has already been taken.': 'Este valor ya está en uso.',
        'characters': 'caracteres'
    };
    
    let translatedError = error;
    Object.keys(errorMessages).forEach(key => {
        if (error.includes(key)) {
            translatedError = translatedError.replace(key, errorMessages[key]);
        }
    });
    
    return translatedError;
};