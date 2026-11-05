<?php

/**
 * Helpers de senha compatíveis com a API Python (bcrypt).
 * Contas antigas (senha em texto puro) ainda logam e são convertidas no primeiro login.
 */

if (! function_exists('senha_hash')) {
    function senha_hash(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }
}

if (! function_exists('senha_eh_hash')) {
    function senha_eh_hash(?string $armazenada): bool
    {
        return is_string($armazenada) && (bool) preg_match('/^\$2[aby]\$/', $armazenada);
    }
}

if (! function_exists('senha_confere')) {
    function senha_confere(string $senha, ?string $armazenada): bool
    {
        if ($armazenada === null || $armazenada === '') {
            return false;
        }

        if (senha_eh_hash($armazenada)) {
            return password_verify($senha, $armazenada);
        }

        // legado: texto puro (comparação em tempo constante)
        return hash_equals($armazenada, $senha);
    }
}

if (! function_exists('senha_precisa_rehash')) {
    function senha_precisa_rehash(?string $armazenada): bool
    {
        return ! senha_eh_hash($armazenada) || password_needs_rehash($armazenada, PASSWORD_BCRYPT);
    }
}
