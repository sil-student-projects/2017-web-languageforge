import {Component, OnChanges, Input, Output, EventEmitter} from '@angular/core'
import {LexEntry} from '../shared/models/lex-entry';

const ENTRIES_PER_PAGE: number = 50;

@Component({
    moduleId: module.id,
    selector: 'word-list',
    templateUrl: 'word-list.component.html',
    styleUrls: ['word-list.component.css']
})
export class WordListComponent implements OnChanges {
    @Input() entries: LexEntry[];
    @Input() wordLanguages: string[];
    @Input() definitionLanguages: string[];
    @Output() onEntrySelected = new EventEmitter<LexEntry>();
    currentPage: number;
    selectedEntry: LexEntry;
    entriesForPage: LexEntry[] = [];

    constructor() {
        this.currentPage = 1;
    }

    ngOnChanges() {
        this.repaginate();
    }

    repaginate() {
        if (this.entries) {
            this.entriesForPage = this.entries
                .slice((this.currentPage - 1) * ENTRIES_PER_PAGE, this.currentPage * ENTRIES_PER_PAGE);
        } else {
            this.entriesForPage = [];
        }
    }

    incrementCurrentPage() {
        this.currentPage++;
    }

    decrementCurrentPage() {
        this.currentPage--;
    }

    /**
     * This function tries to find a matching word for the languages in settings, and if it can't find a match or
     * if the only matches are empty strings, it returns '[Empty]'
     */
    getPreviewWord(entry: LexEntry) {
        for (let language of this.wordLanguages) {
            if (entry.lexeme[language] && entry.lexeme[language].value != '') {
                return entry.lexeme[language].value;
            }
        }
        return '[Empty]';
    }

    /**
     * Same as getPreviewWord(), just a little more verbose because of words without definitions and
     * the fact that the definition is a level deeper than the word.
     */
    getPreviewDefinition(entry: LexEntry) {
        for (let sense of entry.senses) {
            for (let language of this.definitionLanguages) {
                if (sense.definition[language] && sense.definition[language].value != '') {
                    return sense.definition[language].value;
                }
            }
        }
        return '[Empty]';
    }

    selectEntry(entry: LexEntry) {
        this.selectedEntry = entry;
        this.onEntrySelected.emit(entry);
    }

    getMatchingWordForLanguageSettings(entry: LexEntry) {
        for (let language of this.wordLanguages) {
            if (entry.lexeme[language]) {
                return entry.lexeme[language].value;
            }
        }
        return '[Empty]';
    }
}