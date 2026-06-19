/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */



// mobile selectors
const mobileBackBtnSelector = '.js-mobile-menu__back-btn';
const mobileLinkWithSubmenuSelector = '.js-mobile-menu__link--has-submenu';
const mobileAccordionLinkWithSubmenuSelector = '.js-mobile-menu__link-accordion--has-submenu';
const mobileSubmenuSelector = '.js-mobile-menu__submenu';
const mobileHeaderSelector = '.js-mobile-menu__header';
const mobileTitleSelector = '.js-mobile-menu__title';
const mobileTabTitleSelector = '.js-mobile-menu__tab-title';
const mobileSubcatExpanderSelector = '.js-mobile-menu__subcat-expander';
const mobileCloseSelector = '.js-mobile-menu__close';
const mobileLocalizationSelector = '.js-mobile-menu__language-currency';


// mobile classes
const mobileSubmenuActiveClass = 'mobile-menu__submenu--active';
const mobileSubmenuPrevClass = 'mobile-menu__submenu--prev';
const mobileHeaderActiveClass = 'mobile-menu__header--active';
const mobileActiveClass = 'mobile-menu--active';
const mobileSubcatActiveClass = 'mobile-menu__links-list-li--active';
const mobileTabAccordionActiveClass = 'mobile-menu__tab--active';
const mobileLocalizationActiveClass = 'mobile-menu__language-currency--active';

// mobile animation
const mobileInAnimation = 'slideLeft 0.3s ease forwards';
const mobileOutAnimation = 'slideRight 0.3s ease forwards';


export default class TopMenu {
  constructor(mobile) {
    this.menuMobile = document.querySelector(mobile);
    this.init();
  }

  init() {

    this.initVars();
    this.initMobile();

  }

  initVars() {

    // MOBILE

    this.mobileBackBtn = this.menuMobile.querySelector(mobileBackBtnSelector);
    this.mobileTitle = this.menuMobile.querySelector(mobileTitleSelector);
    this.mobileHeader = this.menuMobile.querySelector(mobileHeaderSelector);
    this.mobileLinks = this.menuMobile.querySelectorAll(mobileLinkWithSubmenuSelector);

    this.mobileAccordionLinks = this.menuMobile.querySelectorAll(mobileAccordionLinkWithSubmenuSelector);

    this.mobileSubcatExpanders = this.menuMobile.querySelectorAll(mobileSubcatExpanderSelector);
    this.mobileClose = this.menuMobile.querySelector(mobileCloseSelector);


    this.mobileLocalzationEl = this.menuMobile.querySelector(mobileLocalizationSelector);


    this.submenuActive = [];
    this.mobileTitleArray = [];
  }


  // MOBILE
  initMobile() {
    this.mobileBackBtn.addEventListener('click', (e) => {
      this.hideMobileSubMenu(e);
    });

    this.mobileLinks.forEach((link) => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.showMobileSubMenu(link.parentNode);
      });
    });

    this.mobileAccordionLinks.forEach((link) => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggleMobileSubMenu(link.parentNode);
      });
    });

    this.mobileSubcatExpanders.forEach((expader) => {
      expader.addEventListener('click', (e) => {
        e.preventDefault();
        this.constructor.toggleSubcats(expader.parentNode);
      });
    });


    this.mobileClose.addEventListener('click', (e) => {
      e.preventDefault();
      $('.js-m-nav-btn-menu').dropdown('hide');
    });

    this.mobileLocalzationEl.addEventListener('click', (e) => {
      this.mobileLocalzationEl.classList.toggle(mobileLocalizationActiveClass);
    });

  }


  toggleMobileSubMenu(tab) {

    let activeTab = tab.querySelector(mobileSubmenuSelector);
    tab.classList.toggle(mobileTabAccordionActiveClass);
    activeTab.classList.toggle(mobileSubmenuActiveClass);

  }


  showMobileSubMenu(tab) {

    let prevActiveTab = this.submenuActive[this.submenuActive.length - 1];

    if (prevActiveTab) {
      prevActiveTab.classList.add(mobileSubmenuPrevClass);
    }


    let activeTab = tab.querySelector(mobileSubmenuSelector);
    activeTab.classList.add(mobileSubmenuActiveClass);
    activeTab.style.animation = mobileInAnimation;
    this.submenuActive.push(activeTab)



    this.menuMobile.classList.add(mobileActiveClass);

    const menuTitle = tab.querySelector(mobileTabTitleSelector).textContent;

    this.mobileTitleArray.push(menuTitle)
    this.mobileTitle.textContent = menuTitle;
    this.mobileHeader.classList.add(mobileHeaderActiveClass);
  }

  // Hide Mobile Submenu Function
  hideMobileSubMenu() {
    let activeTab = this.submenuActive.pop();
    if (activeTab) {

      let prevActiveTab = this.submenuActive[this.submenuActive.length - 1];
      if (prevActiveTab) {
        prevActiveTab.classList.remove(mobileSubmenuPrevClass);
      }

      activeTab.style.animation = mobileOutAnimation;
      setTimeout(() => {
        activeTab.classList.remove(mobileSubmenuActiveClass);
      }, 300);



      this.mobileTitleArray.pop();

      if (!this.submenuActive.length) {
        this.menuMobile.classList.remove(mobileActiveClass);
        this.mobileHeader.classList.remove(mobileHeaderActiveClass);
        this.mobileTitle.textContent = '';
      } else {
        let title = this.mobileTitleArray[this.mobileTitleArray.length - 1];
        this.mobileTitle.textContent = title;
      }
    }
  }

  static toggleSubcats(subcatParent) {
    subcatParent.classList.toggle(mobileSubcatActiveClass);
  }

}
