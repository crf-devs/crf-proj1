import { addPopovers } from './_planning';
import { displayAjaxModal } from './_helpers';

const $ = require('jquery');
const moment = require('moment');

function setSlotMisssion(mission, $slot) {
  let missionsText = $slot.data('mission-text') || '';

  if (missionsText) {
    missionsText += '<br>';
  }

  missionsText += $('<span class="badge badge-secondary">').text(mission.type ? mission.type.name : 'mission')[0].outerHTML;
  missionsText += ' ';

  const url = window.location.pathname.indexOf('organizations') >= 0 ? `/organizations/missions/${mission.id}/modal` : `/user/availability/missions/${mission.id}/modal`;
  missionsText += $(`<button type="button" class="btn btn-link" data-toggle="ajax-modal" data-href="${url}">`).text(mission.name)[0].outerHTML;

  $slot.addClass('mission').data('mission-text', missionsText);
}

function slotMisssion(mission, $slot) {
  if (null === mission.startTime) {
    return setSlotMisssion(mission, $slot);
  }

  let slotStart, slotEnd;

  // Test if it's an unix timestamp or a formated date
  const from = $slot.data('from');
  const to = $slot.data('to');
  if (Number.isInteger(from)) {
    slotStart = moment.unix(from).utc();
    slotEnd = moment.unix(to).utc();
  } else {
    slotStart = moment.utc(from);
    slotEnd = moment.utc(to);
  }

  if (slotStart.isBetween(mission.start, mission.end) || slotEnd.isBetween(mission.start, mission.end)) {
    return setSlotMisssion(mission, $slot);
  }
}

function addPlanningMissions(data) {
  const $planning = $('.planning, .availability-table');

  if (!$planning.length) {
    return;
  }

  $planning.find('td.mission').removeClass('mission').removeData('mission-text');

  data.forEach((mission) => {
    mission.assets.forEach((asset) => {
      $planning.find(`tr[data-type="assets"][data-id=${asset.id}] td[data-day]`).each((i, slotEl) => slotMisssion(mission, $(slotEl)));
    });
    mission.users.forEach((user) => {
      $planning.find(`tr[data-type="users"][data-id=${user.id}] td[data-day]`).each((i, slotEl) => slotMisssion(mission, $(slotEl)));
    });
  });

  addPopovers($planning);
}

function handleMissions(data) {
  data.forEach((mission) => {
    mission.start = moment.utc(mission.startTime);
    mission.end = moment.utc(mission.endTime);
  });

  addPlanningMissions(data);
}

function addUserToMission(url) {
  $('.mission-choose').prop('disabled', true);

  $.ajax({
    method: 'POST',
    dataType: 'json',
    url,
    success: function () {
      $('#modal-add-mission').modal('hide');
      fetchMissions();
    },
    error: function () {
      window.alert('Une erreur est survenue pendant la requête');
      $('.mission-choose').prop('disabled', false);
    },
  });
}

export function fetchMissions() {
  let url;

  if ($('.planning').length) {
    url = '/organizations/missions/find' + window.location.search;
  } else {
    url = window.location.pathname + '/missions';
  }

  $.ajax({
    method: 'GET',
    dataType: 'json',
    url,
    success: handleMissions,
    error: function () {
      window.alert('Une erreur est survenue pendant la récupération des missions');
    },
  });
}

export function initMissionsPlanningEvents() {
  $('#modal-add-mission')
    .on('show.bs.modal', function (event) {
      const $modal = $(this);
      const $link = $(event.relatedTarget);
      const url = $link.data('href');

      displayAjaxModal($modal, url);
    })
    .on('hidden.bs.modal', function () {
      const $modal = $(this);
      $modal.find('.loading').show();
      $modal.find('.content').html('');
    });

  $(document).on('click', '.mission-choose', function () {
    addUserToMission($(this).data('href'));
  });
}
