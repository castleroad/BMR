function success(responseJSON) {
  $('#createEditViewModal').addClass('invisible');
  $('#goodModal').removeClass('invisible');
  
  if (typeof responseJSON.data != 'undefined') {
    let refreshUrl = responseJSON.data.refresh_url;
    if (typeof refreshUrl != 'undefined') {
      if (refreshUrl == false) {
          $('#event-'+responseJSON.data.id).remove();
          $('#event-'+responseJSON.data.event_id).remove();
      }else{
        $.ajax({
          type: "GET",
          url: refreshUrl
        })
        .done(function (data) {
          let oldTr = $('#event-'+responseJSON.data.id);
          if (oldTr.length) {
            oldTr[0].outerHTML = data.tr;
          }else{
            $('#user-index-event-table').prepend(data.tr);
          }
        })
        .fail(function (data) {
          fail(data);
        })
      }
    }
  }

  setTimeout(function () {
    $('#goodModal').addClass('invisible');
  }, 2000);
}

function fail(data) {

  if (!data || !data.responseJSON) {
    return;
  }

  if (data.responseJSON.errors) {
    $.each(data.responseJSON.errors, function (key, value) {
      $("#" + key).html(value[0]);
    });
  } else {
    $('#createEditViewModal').addClass('invisible');
    $('#badModal').find('.json-error').html(data.responseJSON.message);
    $('#badModal').removeClass('invisible');
    setTimeout(function () {
      $('#badModal').addClass('invisible');
      $('#badModal').find('.json-error').html('');
    }, 3500);
  }
}

function isButtonSelectHere() {
  var button = document.getElementsByClassName("attendee-status-button");
  if (!button) {
    return;
  }

  var addSelectClass = function () {
    removeSelectClass();
    this.classList.add('selected');
    $('#eventForm').append('<input type="hidden" id="selected-user-status" name="attendees[' + this.dataset.id + '][status]" value="' + this.dataset.value + '"/>');
  }

  var removeSelectClass = function () {
    for (var i = 0; i < button.length; i++) {
      button[i].classList.remove('selected');
      if ($('#selected-user-status').length) {
        $('#selected-user-status').remove();
      }
    }
  }

  for (var i = 0; i < button.length; i++) {
    button[i].addEventListener("click", addSelectClass);
  }
}

function usersPaginateOnScroll() {
  let page = 1;
  let url = $('.paginate-data')[0].dataset.url;  
  let wraper = $('.table-wrapper');
  let previous = 0;
  let now = 0;
  let wait = 150;
  
  wraper.scroll(function() {
      if (page == 0) {
        return;
      }

      now = new Date().getTime();
      if (now - previous < wait ) {
          return;
      }
      previous = now;
      
      if ((wraper.scrollTop() + wraper.height()) >= ($('.paginate-data').height()-100)) {
          page++;
          $.ajax({
              url: url + '?page=' + page,
              type: "GET",
          })
          .done(function(data) {
            if (typeof data.attendees != 'undefined'){
              if (data.attendees == '') {
                page = 0;
                return;
              }
              
              $('.paginate-data').append(data.attendees);
            }
          })
          .fail(function(data) {
              // console.log(data);
          })
      }
  });
}

function createEditViewModalIsReady() {

  if (!$('.dropdown-display-label').length) {
    $('.dropdown-mul-1').dropdown({
      multipleMode: 'label',
      choice: function () {
        if (arguments) {
          multiSelectDataToAttendees(arguments[1]);
        }
      }
    });
  }

  isButtonSelectHere();

  $('.openCreateEditViewModal').off('click').on('click', function (e) {

    $.ajax({
      type: "GET",
      url: e.currentTarget.dataset.url,
      success: appendFormToModal,
      error: fail,
    });

    function appendFormToModal(data) {
      $('.modal-body').html('');
      $('.modal-body').append(data);
      createEditViewModalIsReady();
    }
    $('#createEditViewModal').removeClass('invisible');
  });

  $('#eventForm').on('submit', function (e) {
    e.preventDefault();

    $('label.error').each(function () {
      $(this).html('');
    });

    $.ajax({
      type: "POST",
      url: e.currentTarget.action,
      data: $(this).serialize(),
      success: success,
      error: fail,
    });
  });

  $('.closeModal').on('click', function (e) {
    $('#createEditViewModal').addClass('invisible');
    $('.modal-body').html('');
  });

  $('#allDayBox').on('click', function (e) {
    if ($('#allDayBox').prop('checked') == true) {
      $('.time-block').addClass('invisible');
    } else {
      $('.time-block').removeClass('invisible');
    }
  });

  $('.detachEvent').off('click').on('click', function (e) {
    if (confirm('Do you realy want to LEAVE ' + e.currentTarget.dataset.title + ' event?')) {
      $.ajax({
        type: "PATCH",
        url: e.currentTarget.dataset.url,
        success: success,
        error: fail,
      });
    }
  });

  $('.deleteEvent').off('click').on('click', function (e) {
    if (confirm('Do you realy want to DELETE ' + e.currentTarget.dataset.title + ' event?')) {
      $.ajax({
        type: "DELETE",
        url: e.currentTarget.dataset.url,
        success: success,
        error: fail,
      });
    }
  });
}



// Multi Select START

function removeAttendeeAllTr() {
  $('.dropdown-chose-list').find('.del').each(function (index, el) {
    $(el).trigger('click');
  });
}

function removeAttendeeTr(e) {
  $('.dropdown-chose-list').find('.del').each(function (index, el) {
    if (el.dataset.id == e.currentTarget.dataset.id) {
      $(el).trigger('click');
    }
  });
}

function multiSelectDataToAttendees(selectedUser) {

  if (!selectedUser) return;

  if ($('#attendee' + selectedUser.id + 'tr').length) {
    $('#attendee' + selectedUser.id + 'tr').remove();
  } else {

    let perms = [];
    perms[1] = "View only";
    perms[2] = "Can edit";

    let stats = [];
    stats[1] = "Not going";
    stats[2] = "Maybe";
    stats[3] = "Going";

    let permsSelectOptions = '';
    for (let index = 1; index < perms.length; index++) {
      if (selectedUser.permission == index) {
        permsSelectOptions += '<option value="' + index + '" selected>' + perms[index] + '</option>';
      } else {
        permsSelectOptions += '<option value="' + index + '">' + perms[index] + '</option>';
      }
    }

    let statsSelectButtons = '<main><div class="menu">';
    if ($('#appended-info').length && $('#appended-info')[0].dataset.requser == selectedUser.id) {
      for (let i = 1; i < stats.length; i++) {
        if (selectedUser.status == i) {
          statsSelectButtons += '<a class="attendee-status-button selected" data-id="' + selectedUser.id + '" data-value="' + i + '">' + stats[i] + '</a>' +
                                '<input type="hidden" name="attendees[' + selectedUser.id + '][status]" value="' + selectedUser.status + '"/>';
        } else {
          statsSelectButtons += '<a class="attendee-status-button" data-id="' + selectedUser.id + '" data-value="' + i + '">' + stats[i] + '</a>';
        }

      }
    } else {
      if (typeof stats[selectedUser.status] == 'undefined') {
        statsSelectButtons += '<span>Pending</span>';
      } else {
        statsSelectButtons += '<span>' + stats[selectedUser.status] + '</span>' +
          '<input type="hidden" name="attendees[' + selectedUser.id + '][status]" value="' + selectedUser.status + '"/>';
      }
    }
    statsSelectButtons += '</div></main>';

    if (typeof selectedUser.avatar == 'undefined') {
      selectedUser.avatar = $('#avatars' + selectedUser.id).attr('value');
    }

    let tr =
      '<tr class="w-full" id="attendee' + selectedUser.id + 'tr">' +
      '<td>' +
      '<img class="rounded-full" style="width: 64px;" src="' + selectedUser.avatar + '">' +
      '</td>' +
      '<td>' +
      '<span>' + selectedUser.name + '</span>' +
      '</td>' +
      '<td>' +
      statsSelectButtons +
      '</td>' +
      '<td>' +
      '<select name="attendees[' + selectedUser.id + '][permission]">' +
      permsSelectOptions +
      '</select>' +
      '</td>' +
      '<td>' +
      '<span class="btn btn-danger" onclick="removeAttendeeTr(event);" data-id="' + selectedUser.id + '">Delete</span>' +
      '</td>' +
      '</tr>';

    $('.attendees-block table').append(tr);
  }
}

(function ($) {
  'use strict';

  function noop() { }

  function throttle(func, wait, options) {
    var context, args, result;
    var timeout = null;

    var previous = 0;
    if (!options) options = {};

    var later = function () {
      previous = options.leading === false ? 0 : new Date().getTime();
      timeout = null;
      result = func.apply(context, args);
      if (!timeout) context = args = null;
    };
    return function () {
      var now = new Date().getTime();

      if (!previous && options.leading === false) previous = now;

      var remaining = wait - (now - previous);
      context = this;
      args = arguments;

      if (remaining <= 0 || remaining > wait) {
        clearTimeout(timeout);
        timeout = null;
        previous = now;
        result = func.apply(context, args);
        if (!timeout) context = args = null;

      } else if (!timeout && options.trailing !== false) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };
  }

  var isSafari = function () {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.indexOf('safari') !== -1) {
      return ua.indexOf('chrome') > -1 ? false : true;
    }
  }();

  var settings = {
    readonly: false,
    minCount: 0,
    minCountErrorMessage: '',
    limitCount: Infinity,
    limitCountErrorMessage: '',
    input: '<input type="text" class="no-disabled" maxLength="72" placeholder="Select a member...">',
    data: [],
    searchable: true,
    searchNoData: '<li style="color:#ddd">No connected users</li>',
    init: noop,
    choice: noop,
    extendProps: ['avatar', 'permission', 'status']
  };

  var KEY_CODE = {
    up: 38,
    down: 40,
    enter: 13
  };

  var EVENT_SPACE = {
    click: 'click.iui-dropdown',
    focus: 'focus.iui-dropdown',
    keydown: 'keydown.iui-dropdown',
    keyup: 'keyup.iui-dropdown'
  };

  var ALERT_TIMEOUT_PERIOD = 1000;

  function createTemplate() {
    var isLabelMode = this.isLabelMode;
    var searchable = this.config.searchable;
    var templateSearch = searchable ? '<span class="dropdown-search">' + this.config.input + '</span>' : '';

    return isLabelMode ? '<div class="dropdown-display-label"><div class="dropdown-chose-list">' + templateSearch + '</div></div><div class="dropdown-main">{{ul}}</div>' : '<a href="javascript:;" class="dropdown-display" tabindex="0"><span class="dropdown-chose-list"></span><a href="javascript:;"  class="dropdown-clear-all" tabindex="0">\xD7</a></a><div class="dropdown-main">' + templateSearch + '{{ul}}</div>';
  }

  function minItemsAlert() {
    var _dropdown = this;
    var _config = _dropdown.config;
    var $el = _dropdown.$el;
    var $alert = $el.find('.dropdown-minItem-alert');
    var alertMessage = _config.minCountErrorMessage;
    clearTimeout(_dropdown.itemCountAlertTimer);

    if ($alert.length === 0) {
      if (!alertMessage) {
        alertMessage = 'Minimum selected users is ' + _config.minCount;
      }
      $alert = $('<div class="dropdown-minItem-alert">' + alertMessage + '</div>');
    }

    $el.append($alert);
    _dropdown.itemCountAlertTimer = setTimeout(function () {
      $el.find('.dropdown-minItem-alert').remove();
    }, ALERT_TIMEOUT_PERIOD);
  }

  function maxItemAlert() {
    var _dropdown = this;
    var _config = _dropdown.config;
    var $el = _dropdown.$el;
    var $alert = $el.find('.dropdown-maxItem-alert');
    var alertMessage = _config.limitCountErrorMessage;
    clearTimeout(_dropdown.itemLimitAlertTimer);

    if ($alert.length === 0) {
      if (!alertMessage) {
        alertMessage = 'Max selected users is ' + _config.limitCount;
      }
      $alert = $('<div class="dropdown-maxItem-alert">' + alertMessage + '</div>');
    }

    $el.append($alert);
    _dropdown.itemLimitAlertTimer = setTimeout(function () {
      $el.find('.dropdown-maxItem-alert').remove();
    }, ALERT_TIMEOUT_PERIOD);
  }

  // select-option ul-li
  function selectToDiv(str) {
    var result = str || '';
    // select
    result = result.replace(/<select[^>]*>/gi, '').replace('</select>', '');
    // optgroup
    result = result.replace(/<\/optgroup>/gi, '');
    result = result.replace(/<optgroup[^>]*>/gi, function (matcher) {
      var groupName = /label="(.[^"]*)"(\s|>)/.exec(matcher);
      var groupId = /data\-group\-id="(.[^"]*)"(\s|>)/.exec(matcher);
      return '<li class="dropdown-group" data-group-id="' + (groupId ? groupId[1] : '') + '">' + (groupName ? groupName[1] : '') + '</li>';
    });

    result = result.replace(/<option(.*?)<\/option>/gi, function (matcher) {
      // var value = /value="?([\w\u4E00-\u9FA5\uF900-\uFA2D]+)"?/.exec(matcher);
      var value = $(matcher).val();
      var name = />(.*)<\//.exec(matcher);
      // html selected/disabled，selected="selected","disabled="disabled"
      var isSelected = matcher.indexOf('selected') > -1 ? true : false;
      var isDisabled = matcher.indexOf('disabled') > -1 ? true : false;
      var extendAttr = ''
      var extendProps = matcher.replace(/data-(\w+)="?(.[^"]+)"?/g, function ($1) {
        extendAttr += $1 + ' '
      });

      isSelected = isDisabled;

      return '<li ' + (isSelected ? ' disabled' : ' tabindex="0"') + ' data-value="' + (value || '') + '" class="dropdown-option ' + (isSelected ? 'dropdown-chose' : '') + '" ' + extendAttr + '>' + (name ? name[1] : '') + '</li>';
    });

    return result;
  }

  // object-data select-option
  function objectToSelect(data) {
    var dropdown = this;
    var map = {};
    var result = '';
    var name = [];
    var selectAmount = 0;
    var extendProps = dropdown.config.extendProps;

    if (!data || !data.length) {
      return false;
    }

    $.each(data, function (index, val) {
      // disable selected

      var hasGroup = val.groupId;
      var isDisabled = val.disabled ? ' disabled' : '';
      // var isSelected = val.selected && !isDisabled ? ' selected' : '';
      var isSelected = isDisabled;
      var extendAttr = ''
      $.each(extendProps, function (index, value) {
        if (val.hasOwnProperty(value)) {
          extendAttr += 'data-' + value + '="' + val[value] + '" '
        }
      })

      var temp = '<option' + isDisabled + isSelected + ' value="' + val.id + '" ' + extendAttr + '>' + val.name + '</option>';
      if (isSelected) {

        if (!$('#attendee' + val.id + 'tr').length) {
          multiSelectDataToAttendees(val);
        }

        name.push('<span class="dropdown-selected">' + val.name + '<i class="del" data-id="' + val.id + '"></i></span>');
        selectAmount++;
      }

      if (hasGroup) {
        if (map[val.groupId]) {
          map[val.groupId] += temp;
        } else {
          //  &janking& just a separator
          map[val.groupId] = val.groupName + '&janking&' + temp;
        }
      } else {
        map[index] = temp;
      }
    });

    $.each(map, function (index, val) {
      var option = val.split('&janking&');

      if (option.length === 2) {
        var groupName = option[0];
        var items = option[1];
        result += '<optgroup label="' + groupName + '" data-group-id="' + index + '">' + items + '</optgroup>';
      } else {
        result += val;
      }
    });

    return [result, name, selectAmount];
  }

  // select-option object-data
  function selectToObject(el) {
    var $select = el;
    var result = [];

    function readOption(key, el) {
      var $option = $(el);
      this.id = $option.prop('value');
      this.name = $option.text();
      this.disabled = $option.prop('disabled');
      this.selected = $option.prop('selected');
    }

    $.each($select.children(), function (key, el) {
      var tmp = {};
      var tmpGroup = {};
      var $el = $(el);
      if (el.nodeName === 'OPTGROUP') {
        tmpGroup.groupId = $el.data('groupId');
        tmpGroup.groupName = $el.attr('label');
        $.each($el.children(), $.proxy(readOption, tmp));
        $.extend(tmp, tmpGroup);
      } else {
        $.each($el, $.proxy(readOption, tmp));
      }
      result.push(tmp);
    });

    return result;
  }

  var action = {
    show: function (event) {
      event.stopPropagation();
      var _dropdown = this;
      $(document).trigger('click.dropdown');
      _dropdown.$el.addClass('active');
    },
    search: throttle(function (event) {
      var _dropdown = this;
      var _config = _dropdown.config;
      var $el = _dropdown.$el;
      var $input = $(event.target);
      var intputValue = $input.val();
      var data = _dropdown.config.data;
      var result = [];
      if (event.keyCode > 36 && event.keyCode < 41) {
        return;
      }
      $.each(data, function (key, value) {
        if ((value.groupName && value.groupName.toLowerCase().indexOf(intputValue.toLowerCase()) > -1) || value.name.toLowerCase().indexOf(intputValue.toLowerCase()) > -1 || '' + value.id === '' + intputValue) {
          result.push(value);
        }
      });
      $el.find('ul').html(selectToDiv(objectToSelect.call(_dropdown, result)[0]) || _config.searchNoData);
    }, 300),
    control: function (event) {
      var keyCode = event.keyCode;
      var KC = KEY_CODE;
      var index = 0;
      var direct;
      var itemIndex;
      var $items;
      if (keyCode === KC.down || keyCode === KC.up) {

        direct = keyCode === KC.up ? -1 : 1;
        $items = this.$el.find('[tabindex]');
        itemIndex = $items.index($(document.activeElement));

        if (itemIndex === -1) {
          index = direct + 1 ? -1 : 0;
        } else {
          index = itemIndex;
        }

        index = index + direct;

        if (index === $items.length) {
          index = 0;
        }
        $items.eq(index).focus();
        event.preventDefault();
      }
    },

    multiChoose: function (event, status) {
      var _dropdown = this;
      var _config = _dropdown.config;
      var $select = _dropdown.$select;
      var $target = $(event.target);
      var value = $target.attr('data-value');
      var hasSelected = $target.hasClass('dropdown-chose');
      var selectedName = [];
      var selectedProp;

      if ($target.hasClass('dropdown-display')) {
        return false;
      }

      if (hasSelected) {
        $target.removeClass('dropdown-chose');
        $target.attr('disabled', false);
        _dropdown.selectAmount--;
      } else {
        if (_dropdown.selectAmount < _config.limitCount) {
          $target.addClass('dropdown-chose');

          if (!$target.hasClass('no-disabled')) {
            $target.attr('disabled', true);
          }

          _dropdown.selectAmount++;
        } else {
          maxItemAlert.call(_dropdown);
          return false;
        }
      }

      _dropdown.name = [];

      $.each(_config.data, function (key, item) {
        if ('' + item.id === '' + value) {
          selectedProp = item;
          item.selected = hasSelected ? false : true;
        }
        if (item.selected) {
          selectedName.push(item.name);
          _dropdown.name.push('<span class="dropdown-selected">' + item.name + '<i class="del" data-id="' + item.id + '"></i></span>');
        }
      });

      $select.find('option[value="' + value + '"]').prop('selected', hasSelected ? false : true);

      if (hasSelected && _dropdown.selectAmount < _config.minCount) {
        minItemsAlert.call(_dropdown);
      }


      _dropdown.$choseList.find('.dropdown-selected').remove();
      _dropdown.$choseList.prepend(_dropdown.name.join(''));
      _dropdown.$el.find('.dropdown-display').attr('title', selectedName.join(','));
      _config.choice.call(_dropdown, event, selectedProp);
    },

    singleChoose: function (event) {
      var _dropdown = this;
      var _config = _dropdown.config;
      var $el = _dropdown.$el;
      var $select = _dropdown.$select;
      var $target = $(event.target);
      var value = $target.attr('data-value');
      var hasSelected = $target.hasClass('dropdown-chose');

      if ($target.hasClass('dropdown-chose') || $target.hasClass('dropdown-display')) {
        return false;
      }

      _dropdown.name = [];


      $el.removeClass('active').find('li').not($target).removeClass('dropdown-chose');

      $target.toggleClass('dropdown-chose');
      $.each(_config.data, function (key, item) {

        item.selected = false;
        if ('' + item.id === '' + value) {
          item.selected = hasSelected ? 0 : 1;
          if (item.selected) {
            _dropdown.name.push('<span class="dropdown-selected">' + item.name + '<i class="del" data-id="' + item.id + '"></i></span>');
          }
        }
      });

      $select.find('option[value="' + value + '"]').prop('selected', true);

      _dropdown.name.push('<span class="placeholder">' + _dropdown.placeholder + '</span>');
      _dropdown.$choseList.html(_dropdown.name.join(''));
      _config.choice.call(_dropdown, event);
    },
    del: function (event) {
      var _dropdown = this;
      var _config = _dropdown.config;
      var $target = $(event.target);
      var id = $target.data('id');

      $.each(_dropdown.name, function (key, value) {
        if (value.indexOf('data-id="' + id + '"') !== -1) {
          _dropdown.name.splice(key, 1);
          return false;
        }
      });

      $.each(_dropdown.config.data, function (key, item) {
        if ('' + item.id == '' + id) {
          item.selected = false;
          return false;
        }
      });

      $('#attendee' + id + 'tr').remove();

      _dropdown.selectAmount--;
      _dropdown.$el.find('[data-value="' + id + '"]').removeClass('dropdown-chose').attr('disabled', false);
      _dropdown.$el.find('[value="' + id + '"]').prop('selected', false).removeAttr('selected');
      $target.closest('.dropdown-selected').remove();
      _config.choice.call(_dropdown, event);

      return false;
    },

    clearAll: function (event) {
      var _dropdown = this;
      var _config = _dropdown.config;
      event && event.preventDefault();

      this.$choseList.find('.del').each(function (index, el) {
        $(el).trigger('click');
      });

      if (_config.minCount > 0) {
        minItemsAlert.call(_dropdown);
      }

      this.$el.find('.dropdown-display').removeAttr('title');
      return false;
    }
  };

  function Dropdown(options, el) {
    this.$el = $(el);
    this.$select = this.$el.find('select');
    this.placeholder = this.$select.attr('placeholder');
    this.config = options;
    this.name = [];
    this.isSingleSelect = !this.$select.prop('multiple');
    this.selectAmount = 0;
    this.itemLimitAlertTimer = null;
    this.isLabelMode = this.config.multipleMode === 'label';
    this.init();
  }

  Dropdown.prototype = {
    init: function () {
      var _this = this;
      var _config = _this.config;
      var $el = _this.$el;
      _this.$select.hide();
      // dropdown token
      $el.addClass(_this.isSingleSelect ? 'dropdown-single' : _this.isLabelMode ? 'dropdown-multiple-label' : 'dropdown-multiple');

      if (_config.data.length === 0) {
        _config.data = selectToObject(_this.$select);
      }

      var processResult = objectToSelect.call(_this, _config.data);

      _this.name = processResult[1];
      _this.selectAmount = processResult[2];
      _this.$select.html(processResult[0]);
      _this.renderSelect();
      // disabled readonly
      _this.changeStatus(_config.disabled ? 'disabled' : _config.readonly ? 'readonly' : false);

      _this.config.init();
    },

    // select dropdown
    renderSelect: function (isUpdate, isCover) {
      var _this = this;
      var $el = _this.$el;
      var $select = _this.$select;
      var elemLi = selectToDiv($select.prop('outerHTML'));
      var template;

      if (isUpdate) {
        $el.find('ul')[isCover ? 'html' : 'append'](elemLi);
      } else {
        template = createTemplate.call(_this).replace('{{ul}}', '<ul>' + elemLi + '</ul>');
        $el.append(template).find('ul').removeAttr('style class');
      }

      if (isCover) {
        _this.name = [];
        _this.$el.find('.dropdown-selected').remove();
        _this.$select.val('');
      }

      _this.$choseList = $el.find('.dropdown-chose-list');

      if (!_this.isLabelMode) {
        _this.$choseList.html($('<span class="placeholder"></span>').text(_this.placeholder));
      }

      _this.$choseList.prepend(_this.name ? _this.name.join('') : []);
    },

    bindEvent: function () {
      var _this = this;
      var $el = _this.$el;
      var openHandle = isSafari ? EVENT_SPACE.click : EVENT_SPACE.focus;

      $el.on(EVENT_SPACE.click, function (event) {
        event.stopPropagation();
      });

      $el.on(EVENT_SPACE.click, '.del', $.proxy(action.del, _this));

      // show
      if (_this.isLabelMode) {
        $el.on(EVENT_SPACE.click, '.dropdown-display-label', function () {
          $el.find('input').focus();
        });
        if (_this.config.searchable) {
          $el.on(EVENT_SPACE.focus, 'input', $.proxy(action.show, _this));
        } else {
          $el.on(EVENT_SPACE.click, $.proxy(action.show, _this));
        }
        // $el.on(EVENT_SPACE.keydown, 'input', function (event) {
        // if (event.keyCode === 8 && this.value === '' && _this.name.length) {
        // $el.find('.del').eq(-1).trigger('click');
        // }
        // });
      } else {
        $el.on(openHandle, '.dropdown-display', $.proxy(action.show, _this));
        $el.on(openHandle, '.dropdown-clear-all', $.proxy(action.clearAll, _this));
      }

      $el.on(EVENT_SPACE.keyup, 'input', $.proxy(action.search, _this));

      // enter token
      $el.on(EVENT_SPACE.keyup, function (event) {
        var keyCode = event.keyCode;
        var KC = KEY_CODE;
        if (keyCode === KC.enter) {
          $.proxy(_this.isSingleSelect ? action.singleChoose : action.multiChoose, _this, event)();
        }
      });

      // token
      $el.on(EVENT_SPACE.keydown, $.proxy(action.control, _this));

      $el.on(EVENT_SPACE.click, 'li[tabindex]', $.proxy(_this.isSingleSelect ? action.singleChoose : action.multiChoose, _this));
    },
    unbindEvent: function () {
      var _this = this;
      var $el = _this.$el;
      var openHandle = isSafari ? EVENT_SPACE.click : EVENT_SPACE.focus;

      $el.off(EVENT_SPACE.click);
      $el.off(EVENT_SPACE.click, '.del');

      // show
      if (_this.isLabelMode) {
        $el.off(EVENT_SPACE.click, '.dropdown-display-label');
        $el.off(EVENT_SPACE.focus, 'input');
        $el.off(EVENT_SPACE.keydown, 'input');
      } else {
        $el.off(openHandle, '.dropdown-display');
        $el.off(openHandle, '.dropdown-clear-all');
      }

      $el.off(EVENT_SPACE.keyup, 'input');

      // enter token
      $el.off(EVENT_SPACE.keyup);

      // token
      $el.off(EVENT_SPACE.keydown);
      $el.off(EVENT_SPACE.click, '[tabindex]');
    },
    changeStatus: function (status) {
      var _this = this;
      if (status === 'readonly') {
        _this.unbindEvent();
      } else if (status === 'disabled') {
        _this.$select.prop('disabled', true);
        _this.unbindEvent();
      } else {
        _this.$select.prop('disabled', false);
        _this.bindEvent();
      }
    },
    update: function (data, isCover) {
      var _this = this;
      var _config = _this.config;
      var $el = _this.$el;
      var _isCover = isCover || false;

      if (Object.prototype.toString.call(data) !== '[object Array]') {
        return;
      }

      _config.data = _isCover ? data.slice(0) : _config.data.concat(data);

      var processResult = objectToSelect.call(_this, _config.data);

      _this.name = processResult[1];
      _this.selectAmount = processResult[2];
      _this.$select.html(processResult[0]);
      _this.renderSelect(true, _isCover);
    },
    destroy: function () {
      this.unbindEvent();
      this.$el.children().not('select').remove();
      this.$el.removeClass('dropdown-single dropdown-multiple-label dropdown-multiple');
      this.$select.show();
    },
    choose: function (values, status) {
      var valArr = Object.prototype.toString.call(values) === '[object Array]' ? values : [values];
      var _this = this;
      var _status = status !== void 0 ? !!status : true
      $.each(valArr, function (index, value) {
        var $target = _this.$el.find('[data-value="' + value + '"]');
        var targetStatus = $target.hasClass('dropdown-chose');
        if (targetStatus !== _status) {
          $target.trigger(EVENT_SPACE.click, status || true)
        }
      });
    },
    reset: function () {
      action.clearAll.call(this)
    }
  };

  $(document).on('click.dropdown', function () {
    $('.dropdown-single,.dropdown-multiple,.dropdown-multiple-label').removeClass('active');
  });

  $.fn.dropdown = function (options) {
    this.each(function (index, el) {
      $(el).data('dropdown', new Dropdown($.extend(true, {}, settings, options), el));
    });
    return this;
  }
})(jQuery);

// Multi Select END