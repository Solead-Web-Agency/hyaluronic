/*
 * 2003-2016 Business Tech
 *
 *  @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 *  @copyright 2003-2021 Business Tech SARL
 */

var GmcProFeatureByCat = function(sName) {
  // set name
  this.name = sName;

  // set name
  this.oldVersion = false;

  // set translated js msgs
  this.msgs = {};

  // stock error array
  this.aError = [];

  // set url of admin img
  this.sImgUrl = "";

  // set url of module's web service
  this.sWebService = "";

  // variable to control the generation of the XML content
  this.bGenerateXmlFlag = false;

  //variable to manage autocomplete product for all the module
  this.aParamsAutcomplete = {};

  // set this in obj context
  var oThis = this;

  /**
   * handleOptionToDisplay() manage the dynamic display for the feature by cat tag
   *
   * @param string sTagType
   */
  this.handleOptionToDisplay = function(sTagType) {
    // initialize the list of elt to show and hide
    var aShow = [];
    var aHide = [];

    switch (sTagType) {
      case "material":
        oGmcPro.doSet("#set_tag", "material");
        aShow = ["#bulk_action_material", ".value_material"];
        aHide = [
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          "#bulk_action_tagadult",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "pattern":
        oGmcPro.doSet("#set_tag", "pattern");
        aShow = ["#bulk_action_pattern", ".value_pattern"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_adult",
          "#bulk_action_gender",
          "#bulk_action_tagadult",
          ".value_material",
          ".value_agegroup",
          ".value_gender",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "agegroup":
        oGmcPro.doSet("#set_tag", "agegroup");
        aShow = ["#bulk_action_adult", ".value_agegroup"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_gender",
          "#bulk_action_tagadult",
          ".value_material",
          ".value_pattern",
          ".value_gender",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "gender":
        oGmcPro.doSet("#set_tag", "gender");
        aShow = ["#bulk_action_gender", ".value_gender"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_tagadult",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "adult":
        oGmcPro.doSet("#set_tag", "adult");
        aShow = ["#bulk_action_tagadult", ".value_tagadult"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country'
        ];
        break;
      case "sizeType":
        oGmcPro.doSet("#set_tag", "sizeType");
        aShow = ["#bulk_action_sizeType", ".value_sizeType"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "sizeSystem":
        oGmcPro.doSet("#set_tag", "sizeSystem");
        aShow = ["#bulk_action_sizeSystem", ".value_sizeSystem"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_energy",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "energy":
        oGmcPro.doSet("#set_tag", "energy");
        aShow = ["#bulk_action_energy", ".value_energy"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          ".value_unit_pricing_measure",
          "#bulk_action_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "shipping_label":
        oGmcPro.doSet("#set_tag", "shipping_label");
        aShow = ["#bulk_action_shipping_label", ".value_shipping_label"];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          "#bulk_action_energy_2",
          ".value_energy",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country'
        ];
        break;
      case "unit_pricing_measure":
        oGmcPro.doSet("#set_tag", "unit_pricing_measure");
        aShow = [
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure"
        ];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          "#bulk_action_energy_2",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "base_unit_pricing_measure":
        oGmcPro.doSet("#set_tag", "base_unit_pricing_measure");
        aShow = [
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure"
        ];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          "#bulk_action_energy_2",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_excluded_destination",
          ".value_excluded_destination",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      case "excluded_destination":
        oGmcPro.doSet("#set_tag", "excluded_destination");
        aShow = [
          "#bulk_action_excluded_destination",
          ".value_excluded_destination"
        ];
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          "#bulk_action_tagadult",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          "#bulk_action_energy_2",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
        case 'excluded_country':
            oGmcPro.doSet('#set_tag', 'excluded_country');
						aShow = [
              '#bulk_action_excluded_country', 
              '.value_excluded_country'
            ];
						aHide = [
              "#bulk_action_material",
              "#bulk_action_pattern",
              "#bulk_action_adult",
              "#bulk_action_gender",
              "#bulk_action_tagadult",
              ".value_material",
              ".value_pattern",
              ".value_agegroup",
              ".value_gender",
              ".value_tagadult",
              "#bulk_action_sizeType",
              ".value_sizeType",
              "#bulk_action_sizeSystem",
              ".value_sizeSystem",
              "#bulk_action_energy",
              ".value_energy",
              "#bulk_action_shipping_label",
              ".value_shipping_label",
              "#bulk_action_unit_pricing_measure",
              ".value_unit_pricing_measure",
              "#bulk_action_base_unit_pricing_measure",
              ".value_base_unit_pricing_measure",
              "#bulk_action_excluded_destination",
              ".value_excluded_destination",
						];
						break;	
      case "0":
        aHide = [
          "#bulk_action_material",
          "#bulk_action_pattern",
          "#bulk_action_adult",
          "#bulk_action_gender",
          "#bulk_action_tagadult",
          ".value_material",
          ".value_pattern",
          ".value_agegroup",
          ".value_gender",
          ".value_tagadult",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeType",
          ".value_sizeType",
          "#bulk_action_sizeSystem",
          ".value_sizeSystem",
          "#bulk_action_energy",
          "#bulk_action_energy_2",
          ".value_energy",
          "#bulk_action_shipping_label",
          ".value_shipping_label",
          "#bulk_action_unit_pricing_measure",
          ".value_unit_pricing_measure",
          "#bulk_action_base_unit_pricing_measure",
          ".value_base_unit_pricing_measure",
          '#bulk_action_excluded_country', 
          '.value_excluded_country' 
        ];
        break;
      default:
        break;
    }

    oGmcPro.initHide(aHide);
    oGmcPro.initShow(aShow);
  };
};
