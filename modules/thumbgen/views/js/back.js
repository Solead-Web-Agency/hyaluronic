/**
*  @author    Prestapro
*  @copyright Prestapro
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

var thumbGen = (function() {
  var running = false,
      batch = false,
      checked,
      checkAll,
      startButtons;

  function request(data, done, fail, always) {
    data.secureKey = thumbgen.secureKey;

    $.ajax({
      type: 'POST',
      url: thumbgen.controllerPath,
      dataType : 'json',
      data: data
    })
    .done(function(response) {
      if (typeof done === 'function') {
        done(response);
      }
    })
    .fail(function(response) {
      if (typeof fail === 'function') {
        fail(response);
      }
    })
    .always(function(response) {
      if (typeof always === 'function') {
        always(response);
      }
    });
  }

  function updateCheckAllControl() {
    var isChecked = false;

    if ($('.tg-checkbox').filter(':not(:checked)').length == 0) {
      isChecked = true;
    } else {
      isChecked = false;
    }

    $('#tg-select-all').prop('checked', isChecked);
  }

  function checkFields() {
    checked = $('.tg-checkbox:checked').map(function() {
      return this.value;
    }).get();
  }

  function saveChecked() {
    checkFields();

    request({
      action: 'saveChecked',
      imageTypes: checked
    });
  }

  function saveValue(name, value) {
    request({
      action: 'saveValue',
      name: name,
      value: value
    });
  }

  function updateProgress(parent, percent, processed, total) {
    var progress = parent.find('.tg-progress'),
        number = parent.find('.tg-percent span');

    if (number.text() != percent) {
      progress.animate({width: percent + '%'}, 1200);
      number.text(percent);

      if (percent > 50) {
        number.parent().addClass('tg-light');
      } else {
        number.parent().removeClass('tg-light');
      }
    }

    if (typeof processed !== 'undefined' && processed >= 0) {
      parent.find('.tg-processed').text(processed);
    }

    if (typeof total !== 'undefined' && total >= 0) {
      parent.find('.tg-total').text(total);
    }
  }

  function generateThumbnails(type, mode, parent) {
    running = true;
    startButtons = $('#tg-start-all, .tg-start, .tg-resume');
    startButtons.prop('disabled', true);
    checkFields();

    request({
      action: 'generateThumbnails',
      mode: mode,
      imageGroup: type,
      imageTypes: checked
    },
    function(response) {
      if (response.status == 'error') {
        showErrorMessage(response.message);
      } else {
        if (response.total <= 0) {
          showNoticeMessage(thumbgen.notification.thumbnail_generation.no_images);
          return false;
        }

        if (type != 'skipped' && response.skipped > 0) {
          $('#tg-skipped-count').text(response.skipped);
          $('#tg-skipped-total').text(response.skipped);
          $('#tg-required-memory').text(response.required_memory);
          $('#tg-skipped').fadeIn();
        }

        var percent = response.percent;

        updateProgress(parent, percent, response.processed, response.total);

        if (running === false) {
          showSuccessMessage(thumbgen.notification.thumbnail_generation.stop);
          startButtons.prop('disabled', false);
          updateLog();
          return false;
        }

        if (percent < 100) {
          if (response.processed % 100 === 0) {
            updateLog();
          }

          generateThumbnails(type, 'continue', parent);
        } else {
          updateLog();

          if (type == 'skipped') {
            if (response.skipped > 0) {
              showErrorMessage(thumbgen.notification.thumbnail_generation.incomplete);
              startButtons.prop('disabled', false);
              return false;
            }

            $('#tg-skipped').fadeOut();
          }

          var nextGroup = parent.next('tr');

          if (batch === true && nextGroup.length > 0) {
            generateThumbnails(nextGroup.find('.tg-start').data('type'), 'start', nextGroup);
          } else {
            showSuccessMessage(thumbgen.notification.thumbnail_generation.success);
            startButtons.prop('disabled', false);
          }
        }
      }
    },
    function(response) {
      console.log(response);
      showErrorMessage(thumbgen.notification.thumbnail_generation.error);
      startButtons.prop('disabled', false);
    });
  }

  function clearLog() {
    request({
      action: 'clearLog'
    },
    function() {
      showSuccessMessage(thumbgen.notification.log.success);
      $('#tg-log-contents').empty();
    },
    function(response) {
      showErrorMessage(thumbgen.notification.log.error);
      console.log(response);
    });
  }

  function updateLog() {
    request({
      action: 'showLog'
    },
    function(response) {
      if (response.status != 'error') {
        $('#ppro-log').html(response.message);
      } else {
        console.log(response);
      }
    },
    function(response) {
      console.log(response);
    });
  }

  return {
    init: function() {
      $('.tg-bar').each(function() {
        updateProgress($(this), $(this).data('complete'));
      });

      updateCheckAllControl();

      $('.tg-checkbox').on('change', function() {
        saveChecked();
        updateCheckAllControl();
      });

      $('#tg-select-all').on('change', function() {
        if ($(this).prop('checked') === true) {
          checkAll = true;
        } else {
          checkAll = false;
        }

        $('.tg-checkbox').prop('checked', checkAll);
        saveChecked();
      });

      $('#tg-start-all').on('click', function() {
        batch = true;
        $('.tg-start').first().click();
      });

      $('.tg-start').on('click', function() {
        generateThumbnails($(this).data('type'), 'start', $(this).parents().eq(1));
      });

      $('.tg-resume').on('click', function() {
        generateThumbnails($(this).data('type'), 'continue', $(this).parents().eq(1));
      });

      $('.tg-stop').on('click', function() {
        running = false;
      });

      $('#tg-chunk').on('input', function() {
        $('#tg-chunk-value').val($(this).val());
      });

      $('#tg-chunk').on('change', function() {
        saveValue('chunk', $(this).val());
      });

      $('body').on('click', '#tg-log-download', function() {
        var params = {
          secureKey: thumbgen.secureKey,
          action: 'downloadLog'
        };
        window.location.href = thumbgen.controllerPath + '&' + $.param(params);
      });

      $('body').on('click', '#tg-log-clear', function() {
        if (confirm(thumbgen.notification.log.confirm)) {
          clearLog();
        }
      });

      $('.list-group-item').on('click', function() {
        var el = $(this).parent().closest('.list-group').children('.active');

        if (el.hasClass('active')) {
          el.removeClass('active');
          $(this).addClass('active');
        }
      });

      $('.ppro-faq-question a').on('click', function(event) {
        event.preventDefault();
        $(this).parent().siblings('.ppro-faq-answer').toggleClass('ppro-hidden');
      });
    }
  };
})();

$(function() {
  thumbGen.init();
});
