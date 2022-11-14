"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
(function () {
    var _this = this;
    var bodyClass = "ebwp-is-active";
    var ebwpContainer = document.getElementById('everest-backup-container');
    var ajaxUrl = _everest_backup.ajaxUrl, _nonce = _everest_backup._nonce, locale = _everest_backup.locale, actions = _everest_backup.actions, sseURL = _everest_backup.sseURL;
    var convertToSlug = function (string) {
        return string.toLowerCase()
            .replace(/ /g, '-')
            .replace(/[^\w-]+/g, '');
    };
    var customNameTagSlugify = function () {
        if (!ebwpContainer) {
            return;
        }
        var customNameTag = ebwpContainer.querySelector('#custom-name-tag');
        if (!customNameTag) {
            return;
        }
        customNameTag.addEventListener('input', function (event) {
            var val = this.value;
            this.value = convertToSlug(val);
        });
    };
    var toggleDisplayDeleteFromServer = function () {
        if (!ebwpContainer) {
            return;
        }
        var backupLocationDropdown = ebwpContainer.querySelector('#backup-location-dropdown select');
        var deleteFromServer = ebwpContainer.querySelector('#delete-from-server');
        backupLocationDropdown.addEventListener('change', function () {
            var val = this.value;
            if ('server' !== val) {
                deleteFromServer.style.removeProperty('display');
            }
            else {
                deleteFromServer.style.display = 'none';
            }
        });
        var event = new Event('change');
        backupLocationDropdown.dispatchEvent(event);
    };
    /**
     * Init backup work.
     */
    var Backup = function () {
        if (!ebwpContainer) {
            return;
        }
        var logsContainer = document.getElementById("everest-backup-logs-container");
        var ModalContainer = document.getElementById('everest-backup-modal-wrapper');
        var LoaderWrapper = ModalContainer.querySelector('.loader-wrapper');
        var BackupCompleteModalExtraMsg = ModalContainer.querySelector('#extra-message');
        var BackupCompleteModalFooter = ModalContainer.querySelector('#backup-complete-modal-footer');
        var AfterProcessComplete = ModalContainer.querySelector('.after-process-complete');
        var AfterProcessSuccess = AfterProcessComplete.querySelector('.after-process-success');
        var AfterProcessError = AfterProcessComplete.querySelector('.after-process-error');
        var modalBody = LoaderWrapper.querySelector('.modal-body');
        var btnAbort = modalBody.querySelector("#backup-on-process #btn-abort");
        var processBar = modalBody.querySelector('#process-info .progress .progress-bar');
        var processMsg = modalBody.querySelector('#process-info .process-message');
        var modalDismissWrapper = document.getElementById("btn-modal-dismiss-wrapper");
        var backupForm = ebwpContainer.querySelector("#backup-form");
        var btnBackup = ebwpContainer.querySelector("#btn-backup");
        if (!backupForm) {
            return;
        }
        if (!btnBackup) {
            return;
        }
        /**
         * Reset log container.
         *
         * @param element Log container html element.
         */
        var resetLogContainer = function (element) {
            if (null === element) {
                return;
            }
            element.removeAttribute('open');
            element.classList.add("hidden");
            element.innerHTML = "";
        };
        /**
         * Creates logs and sets it to log container.
         *
         * @param logs Logs.
         * @param element Log container html element.
         * @returns void
         */
        var renderLogs = function (logs, element) {
            if (!logs.length) {
                return;
            }
            if (null === element) {
                return;
            }
            var logsHTML = '<ul class="everest-backup-logs-list">';
            logs.map(function (log, index) {
                var logType = "";
                var _a = log, type = _a.type, message = _a.message;
                if ("undefined" === typeof message) {
                    return;
                }
                logType = type;
                if ("done" === type) {
                    logType = "success";
                }
                logsHTML += "<li class=\"logs-list-item item-key-".concat(index, " notice notice-").concat(logType, "\">").concat(message, "</li>");
            });
            logsHTML += "</ul>";
            element.innerHTML = logsHTML;
            element.classList.remove("hidden");
        };
        /**
         * Toggle modal ui on/off.
         */
        var displayModalUI = function (isActive) {
            if (isActive) {
                btnAbort.classList.remove('hidden');
                document.body.classList.add(bodyClass);
                BackupCompleteModalFooter.innerHTML = '';
                modalDismissWrapper === null || modalDismissWrapper === void 0 ? void 0 : modalDismissWrapper.classList.add("hidden");
                LoaderWrapper.classList.remove('hidden');
                AfterProcessComplete.classList.add('hidden');
                AfterProcessSuccess === null || AfterProcessSuccess === void 0 ? void 0 : AfterProcessSuccess.classList.add('hidden');
                AfterProcessError === null || AfterProcessError === void 0 ? void 0 : AfterProcessError.classList.add('hidden');
            }
            else {
                btnAbort.classList.add('hidden');
                LoaderWrapper.classList.add('hidden');
                AfterProcessComplete.classList.remove('hidden');
                modalDismissWrapper === null || modalDismissWrapper === void 0 ? void 0 : modalDismissWrapper.classList.remove("hidden");
            }
        };
        /**
         * Create button for downloading backup file.
         */
        var btnDownload = function (zipUrl) {
            if (zipUrl === void 0) { zipUrl = ""; }
            var element = document.createElement("a");
            element.id = "zip-download-link";
            element.href = zipUrl;
            element.target = "_blank";
            element.text = locale.zipDownloadBtn;
            element.setAttribute("class", "button");
            return element;
        };
        /**
         * Create button for generating migration key.
         */
        var btnGenerateMigrationKey = function (url) {
            if (url === void 0) { url = ''; }
            var element = document.createElement("a");
            element.id = "generate-migration-key";
            element.href = url;
            element.text = locale.migrationPageBtn;
            element.setAttribute("class", "button");
            return element;
        };
        var onBackupProcessSuccess = function (args, msg) {
            processBar.style.width = '100%';
            BackupCompleteModalExtraMsg.classList.add('hidden'); // Reset.
            /**
             * For smooth UI transition.
             */
            setTimeout(function () {
                displayModalUI(false);
                AfterProcessSuccess.classList.remove('hidden');
                modalDismissWrapper === null || modalDismissWrapper === void 0 ? void 0 : modalDismissWrapper.classList.remove('hidden');
                if (undefined !== msg) {
                    BackupCompleteModalExtraMsg.classList.remove('hidden');
                    var paragraphTag = BackupCompleteModalExtraMsg.querySelector('.process-message');
                    paragraphTag.innerText = msg;
                }
                BackupCompleteModalFooter.appendChild(btnDownload(args.zipurl));
                BackupCompleteModalFooter.appendChild(btnGenerateMigrationKey(args.migration_url));
            }, 1000);
        };
        var onBackupProcessError = function () {
            displayModalUI(false);
            AfterProcessError.classList.remove('hidden');
        };
        var handleProgressInfo = function (message, progress) {
            processBar.style.width = "".concat(progress, "%");
            if ('undefined' !== typeof message) {
                processMsg.innerText = message;
            }
        };
        var removeProcStatFile = function () {
            navigator.sendBeacon("".concat(sseURL, "?unlink=1"));
        };
        /**
         * Handle everything related to backup process statistics.
         */
        var handleProcStats = function (beaconSent) {
            var onBeaconSent = function () { return __awaiter(_this, void 0, void 0, function () {
                var response, result;
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0: return [4 /*yield*/, fetch(sseURL)];
                        case 1:
                            response = _a.sent();
                            result = response.json();
                            result.then(function (res) {
                                switch (res.status) {
                                    case 'done':
                                        removeProcStatFile();
                                        renderLogs(res.data.logs, logsContainer);
                                        onBackupProcessSuccess(res.data.result);
                                        break;
                                    case 'cloud':
                                        removeProcStatFile();
                                        onBackupProcessSuccess(res.data, res.message);
                                        break;
                                    case 'error':
                                        removeProcStatFile();
                                        onBackupProcessError();
                                        break;
                                    default:
                                        handleProgressInfo(res.message, res.progress);
                                        onBeaconSent();
                                        break;
                                }
                            }).catch(function (err) {
                                console.warn(err);
                                setTimeout(function () { return onBeaconSent(); }, 1000); // Retry again after 1 seconds.
                            });
                            ;
                            return [2 /*return*/];
                    }
                });
            }); };
            var onBeaconFailed = function () {
                removeProcStatFile();
            };
            if (beaconSent) {
                onBeaconSent();
            }
            else {
                onBeaconFailed();
            }
        };
        /**
         * Start backup related work on backup button clicked.
         */
        backupForm &&
            backupForm.addEventListener("submit", function (event) {
                event.preventDefault();
                handleProgressInfo(locale.initializingBackup, 0); // Reset progress.
                resetLogContainer(logsContainer); // Reset Logs.
                removeProcStatFile(); // Remove old PROCSTAT file before starting backup.
                var data = {};
                var formData = new FormData(backupForm);
                formData.forEach(function (value, key) {
                    data[key] = value;
                });
                var t = +new Date();
                /**
                 * Send request to start backup.
                 *
                 * @since 1.0.7
                 */
                var beaconSent = navigator.sendBeacon("".concat(ajaxUrl, "?action=").concat(actions.export, "&everest_backup_ajax_nonce=").concat(_nonce, "&t=").concat(t), JSON.stringify(data));
                displayModalUI(beaconSent);
                setTimeout(function () {
                    handleProcStats(beaconSent);
                }, 1500);
            });
        /**
         * Handle backup process abort.
         */
        btnAbort &&
            btnAbort.addEventListener('click', function (event) {
                event.preventDefault();
                if (confirm(locale.abortAlert)) {
                    window.location.reload();
                }
            });
    }; // Backup.
    /**
     * Settings for schedule backup fields.
     */
    var ScheduleBackup = function () {
        var enableDisableCheckbox = ebwpContainer.querySelector("#schedule-backup #enable-disable");
        var tableRows = ebwpContainer.querySelectorAll("#schedule-backup .schedule-backup-table-rows");
        var eventListenerCallback = function () {
            tableRows.forEach(function (tableRow) {
                if (enableDisableCheckbox.checked) {
                    tableRow === null || tableRow === void 0 ? void 0 : tableRow.classList.remove("hidden");
                }
                else {
                    tableRow === null || tableRow === void 0 ? void 0 : tableRow.classList.add("hidden");
                }
            });
        };
        eventListenerCallback();
        enableDisableCheckbox === null || enableDisableCheckbox === void 0 ? void 0 : enableDisableCheckbox.addEventListener("input", eventListenerCallback);
    };
    /**
     * After document is fully loaded.
     */
    window.addEventListener("load", function () {
        document.body.classList.remove(bodyClass);
        Backup();
        ScheduleBackup();
        toggleDisplayDeleteFromServer();
        customNameTagSlugify();
    });
})();
//# sourceMappingURL=backup.js.map