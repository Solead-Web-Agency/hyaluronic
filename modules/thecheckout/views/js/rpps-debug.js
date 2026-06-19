// Script de debug pour vérifier le champ "other"
console.log('=== DEBUG RPPS ===');
console.log('Recherche du champ other...');

// Chercher toutes les occurrences possibles
const selectors = [
    'input[name="other"]',
    'input[name*="other"]',
    'input#field-other',
    '.form-group.other input',
    '.other input[type="text"]',
    '[class*="other"] input',
    'input[id*="other"]'
];

selectors.forEach(selector => {
    const elements = document.querySelectorAll(selector);
    if (elements.length > 0) {
        console.log(`Trouvé avec ${selector}:`, elements);
        elements.forEach(el => {
            console.log('- Element:', el);
            console.log('- Parent form-group:', el.closest('.form-group'));
            console.log('- Visible:', el.offsetParent !== null);
        });
    }
});

// Vérifier tous les champs input
console.log('\nTous les champs input dans les formulaires d\'adresse:');
document.querySelectorAll('#delivery-address input, #invoice-address input').forEach(input => {
    console.log(`- ${input.name || input.id || 'sans nom'}: ${input.type}`);
}); 