<?php

/*
|--------------------------------------------------------------------------
| Mensajes de validación en español
|--------------------------------------------------------------------------
|
| Solo se traducen las reglas que la aplicación usa hoy. Al añadir una regla
| nueva, se añade aquí su mensaje: la interfaz no muestra mensajes en inglés.
|
*/

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'boolean' => 'El campo :attribute debe indicar sí o no.',
    'exists' => 'El valor seleccionado para :attribute no es válido.',
    'in' => 'El valor seleccionado para :attribute no está permitido.',
    'required_if' => 'El campo :attribute es obligatorio para esta operación.',
    'file' => 'El campo :attribute debe contener un archivo válido.',
    'uploaded' => 'No se pudo recibir el archivo :attribute.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'unique' => 'El valor del campo :attribute ya está registrado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'max' => [
        'file' => 'El archivo :attribute no debe superar :max kilobytes.',
        'string' => 'El campo :attribute no debe superar :max caracteres.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
    ],
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'numbers' => 'La contraseña debe contener al menos un número.',
        'mixed' => 'La contraseña debe contener mayúsculas y minúsculas.',
        'uncompromised' => 'Esta contraseña apareció en una filtración de datos. Elige otra.',
    ],

    'attributes' => [
        'name' => 'nombre completo',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'title' => 'título',
        'code' => 'código',
        'description' => 'descripción',
        'ficha_id' => 'ficha',
        'especialidad_id' => 'especialidad',
        'membership_id' => 'membresía',
        'user_id' => 'usuario',
        'lock_version' => 'versión de edición',
        'active' => 'habilitación',
        'role' => 'rol',
        'content' => 'contenido',
        'summary' => 'resumen',
        'activity_assignment_id' => 'asignación de guía',
        'operation' => 'operación',
        'motivo_excepcion' => 'justificación de la excepción',
        'institution_name' => 'nombre de la institución',
        'institution_code' => 'código de la institución',
    ],
];
