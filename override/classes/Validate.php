<?php
/**
 * Override de compatibilité PS9 : réintroduit Validate::isAnything(), supprimée en PrestaShop 9 mais encore
 * déclarée comme règle de validation de champs par des modules 1.7 : dhlexpress (étiquettes), ph_simpleblog
 * (blog), groupinc (règles de prix), masseditproduct… Sans elle, l'enregistrement échoue avec
 * « Fonction de validation non trouvée : isAnything ». Comportement PS 1.7 : toute valeur est acceptée.
 */
class Validate extends ValidateCore
{
    public static function isAnything()
    {
        return true;
    }
}
