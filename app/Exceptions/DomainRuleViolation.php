<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Violación de un invariante de dominio. Se traduce en error de validación o 422 en la capa HTTP;
 * nunca se usa para ocultar un fallo de autorización.
 */
class DomainRuleViolation extends RuntimeException
{
    //
}
