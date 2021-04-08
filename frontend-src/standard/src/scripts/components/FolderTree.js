import { contextmenu } from 'easycontext';
import Config from '../config/config';
const axios = require('axios');
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import Tree from '../twig/tree.html.twig';
import Content from '../twig/content.html.twig';

class FolderTree {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');
    this.dragged;

    const btn = this.container.querySelector('.js-ft-rename-el');
    const inputRename = this.container.querySelector('.folderTree__rename__input');

    if (!document.querySelector('.js-disable-context-menu')) {
      if (this.container.querySelectorAll('.folderTree__link').length) {
        contextMenu(this.container);
      }
    }

    if (btn) {
      btn.addEventListener('click', () => {
        this.renameEl();
      });
    }

    if (inputRename) {
      inputRename.addEventListener('keyup', (e) => {
        if (e.keyCode === 13) {
          this.renameEl();
          e.preventDefault();
        }
      });
    }

    this.container.addEventListener('contextmenu', (event) => {
      if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
        this.container.querySelector('.context-menu').classList.remove('menu-visible');
      }
    });

    this.container.addEventListener('click', (e) => { this.clickOnFolder(e, this.container, this.type); });

    if (!document.querySelector('.js-disable-sortable')) {
      initSortable(this.container, this.type);
    }
    if (!document.querySelector('.js-disable-drag-and-drop')) {
      initDragDrop(this.container, this.type);
    }
  }

  clickOnFolder(event, container, type) {
    const target = event.target;
    const el = target.closest('.folderTree__link');
    this.container = container;
    this.type = type;

    if (el) {
      const isOpen = el.classList.contains('ft-is-folderOpen')
      const noClick = el.classList.contains('ft-no-click');

      if (!noClick) {
        const els = this.container.querySelectorAll('.folderTree__link');

        if (els) {
          els.forEach(el => {
            el.classList.remove('ft-is-selected');
            if (!el.classList.contains('ft-has-child') && !el.classList.contains('ft-is-root')) {
              el.classList.remove('ft-is-folderOpen');
            }
          });
        }
        el.classList.add('ft-is-selected');

        if (isOpen) {
          el.classList.remove('ft-is-folderOpen');
        }
        const uls = el.parentNode.querySelectorAll('.folderTree__ul');
        uls.forEach(ul => {
          ul.remove();
        });

        el.classList.add('ft-is-folderOpen');

       const elId = el.getAttribute('data-id');
       const getLoData = getApiUrl('get', elId, { type: this.type });

       axios.get(getLoData).then((response) => {
         const child = Tree(response.data);
         const childView = Content(response.data);
         const folderView = this.container.querySelector('.folderView');
         const inputParent = this.container.querySelector('#treeview_selected_' + this.type);
         const inputState = this.container.querySelector('#treeview_state_' + this.type);
         inputParent.value = elId;
         inputState.value = response.data.currentState;
         el.insertAdjacentHTML('afterend', child);
         folderView.innerHTML = childView;

         if (!document.querySelector('.js-disable-context-menu')) {
           contextMenu(this.container, this.type);
         }

         if (!document.querySelector('.js-disable-sortable')) {
           initSortable(this.container, this.type);
         }

         if (!document.querySelector('.js-disable-drag-and-drop')) {
           initDragDrop(this.container, this.type);
         }
       }).catch((error) => {
         console.log(error)
       });

        event.preventDefault();
      }
    }
  }

  renameEl() {
    const rename = this.container.querySelector('.folderTree__rename');
    const input = this.container.querySelector('.folderTree__rename__input');
    const value = input.value;
    const el = input.parentNode.parentNode;
    const elId = el.getAttribute('data-id');
    const renameLoData = getApiUrl('rename', elId, { type: this.type, newName: value });

    axios.get(renameLoData).then().catch( (error) => {
      console.log(error);
    });

    rename.classList.remove('is-show');
    el.childNodes[0].innerHTML = value;
    el.classList.remove('ft-no-click');

    this.container.querySelector('.folderView').querySelector('.folderView__li[data-id="' + elId + '"]').querySelector('.folderView__label').innerHTML = value;
  }

}

function getApiUrl(action, id, params) {
  let url = `${Config.apiUrl}lms/lo/${action}&id=${id}`;
  if (!params) {
    params = {};
  }
  url += '&' + new URLSearchParams(params).toString();

  return url;
}

function initSortable(container, type) {
  const view = container.querySelector('.js-sortable-view');

  if (view) {
    new Sortable.create(view, {
      draggable: '.folderView__li',
      animation: 150,
      easing: 'cubic-bezier(1, 0, 0, 1)',
      fallbackOnBody: true,
      invertSwap: true,
      swapThreshold: 0.43,
      onUpdate: function (evt) {
        console.log(evt, 'evt');
        const currentElement = evt.item;
        const currentElementId = currentElement.getAttribute('data-id');
        const parentElement = container.querySelector('.ft-is-selected');
        const childElement = container.querySelector('.folderView__ul').querySelectorAll('.folderView__li');
        const childElementArray = [];
        let parentElementId = 0;
        console.log('current: ' + currentElementId);

        if (parentElement) {
          parentElementId = parentElement ? parentElement.id : 0;
          console.log('parent: ' + parentElementId);
        }

        childElement.forEach(el => {
          const elId = el.getAttribute('data-id');
          childElementArray.push(elId);
        });

        console.log('child order: ' + childElementArray);
        console.log('type: ' + type);

        const reorderLoData = getApiUrl('reorder', currentElementId, { type, newParent: parentElementId, newOrder: childElementArray });
        axios.get(reorderLoData).then().catch( (error) => {
          console.log(error);
        });
      }
    });
  }
}

function initDragDrop(container, type) {
    let currentEl, currentElId;

    container.addEventListener('dragstart', (event) => {
      if (event.target.classList.contains('is-droppable')) {
        currentEl = event.target;
        currentElId = currentEl.getAttribute('data-id');
      }
    });

    container.addEventListener('dragover', (event) => {
      const target = event.target;

      if (currentEl) {
        if ( (currentElId !== target.getAttribute('data-id')) && (target.classList.contains('is-dropzone')) ) {
          target.classList.add('fv-is-dropped');
          event.preventDefault();
        }
      }
    });

    container.addEventListener('dragleave', (event) => {
      const target = event.target;

      if (currentEl) {
        if ((currentElId !== target.getAttribute('data-id')) && (target.classList.contains('is-dropzone'))) {
          target.classList.remove('fv-is-dropped');
        }
      }
    });

    container.addEventListener('drop', (event) => {
      const target = event.target;
      target.classList.remove('fv-is-dropped');

      if (currentEl) {
        if ( (currentElId !== target.getAttribute('data-id')) && (target.classList.contains('is-dropzone')) ) {
          const reorderLoData = getApiUrl('reorder', currentElId, { type, newParent: event.target.getAttribute('data-id') });
          axios.get(reorderLoData).then(() => {
            if (target.classList.contains('ft-is-folderOpen') && (currentEl.classList.contains('folderTree__li') )) {
              const nextElementSibling = target.nextElementSibling;
              if (nextElementSibling.classList.contains('folderTree__ul')) {
                nextElementSibling.appendChild(currentEl);
              } else {
                currentEl.remove();
              }
            } else {
              currentEl.remove();
            }
            container.querySelector('.folderView__li[data-id="' + currentElId + '"]').remove();
          }).catch((error) => {
            console.log(error);
          });
        }
      }
    });
}

function contextMenu(container, type) {
  contextmenu('.folderTree__link:not(.ft-is-root)', (target) => {
    return [
      {
        text: 'Rinomina',
        onClick() {
          const rename = container.querySelector('.folderTree__rename');
          const renameInput = container.querySelector('.folderTree__rename__input');

          if (target.classList.contains('folderTree__rename__input') === false) {
            if (target.hasAttribute('data-id')) {
              target.classList.add('ft-no-click');
              target.appendChild(rename);
            } else {
              target.parentNode.classList.add('ft-no-click');
              target.parentNode.appendChild(rename);
            }
            rename.classList.add('is-show');
            renameInput.focus();
            renameInput.setAttribute('value', target.textContent);

            // Rendo tutti gli elementi non cliccabile se sono in modalità rinomina
            const elsNotClick = container.querySelectorAll('.ft-no-click');
            if (elsNotClick) {
              for (let el of elsNotClick) {
                el.addEventListener('click', (e) => {
                  e.preventDefault();
                })
              }
            }

            // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
            container.addEventListener('click', (event) => {
              if (event.detail) { // fix trigger click se premo su spazio
                const clickInside = rename.contains(event.target);
                if (event.target.classList.contains('menu-item-clickable')) {
                  event.stopPropagation();
                } else {
                  if (!clickInside) {
                    renameInput.blur();
                    rename.classList.remove('is-show');
                  }
                }
              }
            });
          }
        }
      },
      {
        text: 'Elimina',
        onClick() {
          let siblings;
          let elId;

          if (target.hasAttribute('data-id')) {
            siblings = target.parentNode.children;
            target.parentNode.querySelector('.folderTree__link').remove();
            elId = target.getAttribute('data-id');
          } else {
            siblings = target.parentNode.parentNode.children;
            target.parentNode.parentNode.querySelector('.folderTree__link').remove();
            elId = target.parentNode.getAttribute('data-id');
          }

          container.querySelector('.folderView').querySelector('.folderView__li[data-id="' + elId + '"]').parentNode.remove();

          if (siblings) {
            for (let el of siblings) {
              if (el.classList.contains('folderTree__ul')) {
                el.classList.remove('folderTree__ul');
              }
            }
          }

          const deleteLoData = getApiUrl('delete', elId, { type });
          axios.get(deleteLoData).then().catch((error) => {
            console.log(error);
          });
        }
      }
    ];
  });
}

export default FolderTree
