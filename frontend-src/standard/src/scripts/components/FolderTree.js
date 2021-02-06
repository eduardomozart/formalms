import { contextmenu } from 'easycontext';
import Config from '../config/config';
const axios = require('axios');
import Tree from '../twig/tree.html.twig';
//import Content from '../twig/content.html.twig';

class FolderTree {

  constructor() {
    this.config = {
      'get': 'lms/lo/get&id=',
      'rename': 'lms/lo/rename&id='
    }

      const btn = document.querySelector('.js-ft-rename-el');
      const inputRename = document.querySelector('.folderTree__rename__input');

      if (document.querySelectorAll('.folderTree__link').length) {
        contextMenu();
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
          }
        });
      }

      document.addEventListener('contextmenu', (event) => {
        if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
          document.querySelector('.context-menu').classList.remove('menu-visible');
        }
      });

      document.addEventListener('click', this.clickOnFolder);
  }

  clickOnFolder(event) {
    const target = event.target;
    const el = target.closest('.folderTree__link');

    if (el) {
      const isOpen = el.classList.contains('ft-is-folderOpen')
      const noClick = el.classList.contains('ft-no-click');

      if (!noClick) {
        if (isOpen) {
          el.classList.remove('ft-is-folderOpen');
          el.parentNode.querySelector('.folderTree__ul').remove();
        } else {
          const elId = el.getAttribute('id');
          const getLoData = Config.apiUrl + this.config.get + elId;
          el.classList.add('ft-is-folderOpen');
          axios.get(getLoData).then( (response) => {
            const child = Tree(response);
            el.insertAdjacentHTML('afterend',child);
            contextMenu();
          }).catch( (error) => {
            console.log(error)
          });
          event.preventDefault();
        }
      }
    }

  }

  renameEl() {
    const rename = document.querySelector('.folderTree__rename');
    const input = document.querySelector('.folderTree__rename__input');
    const value = input.value;
    const el = input.parentNode.parentNode;
    const elId = el.getAttribute('id');
    const renameLoData = Config.apiUrl + this.config.rename + elId + '&newName=' + value;

    axios.get(renameLoData).then().catch( (error) => {
      console.log(error);
    });

    rename.classList.remove('is-show');
    el.childNodes[0].innerHTML = value;
    el.classList.remove('ft-no-click');

    document.querySelector('#fv-' + elId).querySelector('.folderView__label').innerHTML = value;
  }

}

function contextMenu() {
  contextmenu('.folderTree__link', (target) => {
    return [
      {
        text: 'Rinomina',
        onClick() {
          const rename = document.querySelector('.folderTree__rename');
          const renameInput = document.querySelector('.folderTree__rename__input');

          if (target.classList.contains('folderTree__rename__input') === false) {
            if (target.hasAttribute('id')) {
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
            const elsNotClick = document.querySelectorAll('.ft-no-click');
            if (elsNotClick) {
              for (let el of elsNotClick) {
                el.addEventListener('click', (e) => {
                  e.preventDefault();
                })
              }
            }

            // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
            document.addEventListener('click', (event) => {
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

          if (target.hasAttribute('id')) {
            siblings = target.parentNode.children;
            target.parentNode.querySelector('.folderTree__link').remove();
            elId = target.getAttribute('id');
          } else {
            siblings = target.parentNode.parentNode.children;
            target.parentNode.parentNode.querySelector('.folderTree__link').remove();
            elId = target.parentNode.getAttribute('id');
          }

          document.querySelector('#fv-' + elId).parentNode.remove();

          if (siblings) {
            for (let el of siblings) {
              if (el.classList.contains('folderTree__ul')) {
                el.classList.remove('folderTree__ul');
              }
            }
          }

          const deleteLoData = Config.apiUrl + 'lms/lo/delete&id=' + elId;
          axios.get(deleteLoData).then().catch( (error) => {
            console.log(error);
          });

        }
      }
    ]
  })
}

export default FolderTree
