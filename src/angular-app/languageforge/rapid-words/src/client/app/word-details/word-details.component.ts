import {Component, ViewChildren, QueryList, Input, Output, EventEmitter} from '@angular/core';
import {MultitextComponent} from '../multitext/multitext.component';
import {LexEntry} from '../shared/models/lex-entry';
import {LfApiService} from '../shared/services/lf-api.service';

@Component({
    moduleId: module.id,
    selector: 'worddetails',
    templateUrl: 'word-details.component.html',
    styleUrls: ['word-details.component.css'],
})
export class WordDetailsComponent {
    @Input() public wordMultitextLanguages: string[] = [];
    @Input() public definitionMultitextLanguages: string[] = [];
    @Input() selectedEntry: LexEntry;
    @Output() onEntryAdded = new EventEmitter<LexEntry>();

    @ViewChildren(MultitextComponent) multitextBoxes: QueryList<MultitextComponent>;

    showDetails: Boolean = false;
    detailLabels: any[] = ["Citation Form", "Pronunciation", "CV Pattern", "Tone"];
    id = "";

    constructor(private lfApiService: LfApiService) {
    }

    toggleShowDetails() {
        this.showDetails = !this.showDetails;
    }

    addEntry() {
        let lexEntry = this.constructLexEntryFromMultitexts();
        this.onEntryAdded.emit(lexEntry);
        console.log(`addEntry ${lexEntry}`);
        this.lfApiService
            .addEntry(lexEntry)
            .subscribe(response => {});
    }

    constructLexEntryFromMultitexts(): LexEntry {
        var lexEntry = new LexEntry();
        this.multitextBoxes.forEach(multitextBox => {
            multitextBox.addLexemeOrSenseToLexEntry(lexEntry);
        });

        return lexEntry;
    }
}