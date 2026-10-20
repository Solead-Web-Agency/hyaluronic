"""Mise en page « charte HFM » des emails PrestaShop (thème modern, MJML) — sans toucher aux textes.
python3 -I mail_restyle.py <fichier.html>...   (réécrit les fichiers ; idempotent)"""
import re
import sys

MARK = '<!-- HFM-STYLE v1 -->'
GREEN = '#8CC63F'
GREEN_DARK = '#4F7A1A'
BAND = ('<div style="height:6px;line-height:6px;font-size:0;mso-line-height-rule:exactly;'
        'background-color:#8CC63F;background-image:linear-gradient(90deg,#9BD159,#74B033);'
        'border-radius:16px 16px 0 0;">&nbsp;</div>')
SERIF = "font-family:'Spectral', Georgia, 'Times New Roman', serif"


def block(s, name, fn):
    """Applique fn au contenu entre <!-- NAME BEGINING --> et <!-- NAME ENDING --> (toutes occurrences)."""
    pat = re.compile(r'(<!-- ' + name + r' BEGINING -->)(.*?)(<!-- ' + name + r' ENDING -->)', re.S)
    return pat.sub(lambda m: m.group(1) + fn(m.group(2)) + m.group(3), s)


def restyle(s):
    if MARK in s:
        return s
    # Carte principale : coins arrondis, ombre douce, liseré, bandeau vert en tête.
    s = re.sub(
        r'(<div class="shadow wrapper-container" style=")[^"]*(")',
        r'\1box-shadow: 0 22px 44px -18px rgba(52, 53, 47, 0.28); background: #ffffff; background-color: #ffffff; '
        r'margin: 0px auto; border-radius: 16px; border: 1px solid #ECEAE3; overflow: hidden; max-width: 604px;\2',
        s, count=1)
    s = re.sub(r'(<div class="shadow wrapper-container"[^>]*>)', r'\1' + BAND, s, count=1)

    # Salutation : titre en Spectral, plus grand (styles écrits « compact » ou « espacé » selon le modèle).
    s = re.sub(r"font-family:'Hanken Grotesk', Arial, Helvetica, sans-serif; ?font-size: ?20px; ?font-weight: ?600; ?line-height: ?25px;",
               SERIF + "; font-size: 28px; font-weight: 400; line-height: 36px; letter-spacing: -0.01em;", s)

    # Trait d'accent sous la salutation : vert, plus long.
    def border(b):
        b = b.replace('solid 3px #55606F', 'solid 3px ' + GREEN)
        b = re.sub(r'margin: ?0px auto; ?width: ?25px;', 'margin:0px;width:44px;border-radius:3px;', b)
        b = re.sub(r'width="25(px)?"', r'width="44\1"', b)
        b = b.replace('mj-column-px-25', 'mj-column-px-44').replace('vertical-align:top;width:25px;', 'vertical-align:top;width:44px;')
        return b
    s = block(s, 'BORDER', border)

    # Sous-titres (« Détails de la commande », « Identifiant de commande … ») : petites capitales vertes.
    s = re.sub(
        r"font-family:'Hanken Grotesk', Arial, Helvetica, sans-serif; ?font-size: ?16px; ?font-weight: ?600; ?line-height: ?25px;",
        "font-family:'Hanken Grotesk', Arial, Helvetica, sans-serif; font-size: 12.5px; font-weight: 700; line-height: 20px; "
        "letter-spacing: 0.09em; text-transform: uppercase; color: " + GREEN_DARK + ";", s)
    s = re.sub(r'(letter-spacing: 0\.09em; text-transform: uppercase; color: #4F7A1A;[^"]*?)color: ?#2B2B2B;', r'\1', s)

    # Encadrés : fond crème, liseré vert à gauche, coins arrondis.
    s = re.sub(
        r'background-color: ?#fefefe; ?border: ?1px solid #E7E3DA;',
        'background-color: #FAFAF7; border: 1px solid #ECEAE3; border-left: 4px solid ' + GREEN + '; border-radius: 12px;', s)
    s = s.replace('bgcolor="#fefefe"', 'bgcolor="#FAFAF7"')

    # Tableau produits (confirmation de commande) : en-têtes crème en petites capitales.
    s = re.sub(r'<th bgcolor="#FDFDFD" style="([^"]*?)background-color: ?#FDFDFD; ?color: ?#2B2B2B; ?font-weight: ?600;',
               r'<th bgcolor="#F3F4EF" style="\1background-color: #F3F4EF; color: #55606F; font-weight: 700; '
               r'text-transform: uppercase; letter-spacing: 0.02em; font-size: 10px;', s)
    # Ligne « Total payé » mise en valeur (libellé + montant).
    def total_row(m):
        return re.sub(r'(bgcolor="|background-color: ?)#FDFDFD', r'\1#EEF6E2', m.group(0)).replace(
            'color: #2B2B2B; font-weight: 600; font-size: 14px;', 'color: #3F6B12; font-weight: 700; font-size: 15px;')
    s = re.sub(r'<td[^>]*>[^<]*</td>\s*<td[^>]*>\s*\{total_paid\}\s*</td>', total_row, s)

    # Texte courant un peu plus doux et aéré.
    s = re.sub(r"sans-serif; ?font-size: ?14px; ?line-height: ?25px; ?text-align: ?(left|center|right); ?color: ?#2B2B2B;",
               r"sans-serif; font-size: 14.5px; line-height: 24px; text-align: \1; color: #34352F;", s)


    # Pied de page : nom de la boutique en Spectral, contact à la place de « Powered by PrestaShop ».
    def footer(b):
        b = re.sub(r'Powered by <a href="https://www\.prestashop\.com/[^"]*"[^>]*>PrestaShop</a>',
                   '<a href="mailto:sales@hyaluronicfillermarket.com" style="color: #55606F; text-decoration: none;">'
                   'sales@hyaluronicfillermarket.com</a>', b)
        b = re.sub(r'(<a href="\{shop_url\}" style=")text-decoration: ?underline; ?color: ?#55606F;?',
                   r"\1text-decoration: none; color: #2B2B2B; " + SERIF + "; font-size: 17px;", b)
        return b
    s = block(s, 'SHOP NAME', footer)
    return s.replace('</body>', MARK + '\n</body>', 1)


if __name__ == '__main__':
    n = 0
    for f in sys.argv[1:]:
        src = open(f, encoding='utf-8').read()
        out = restyle(src)
        if out != src:
            open(f, 'w', encoding='utf-8').write(out)
            n += 1
    print(n, 'fichier(s) restylé(s) sur', len(sys.argv) - 1)
