import { Component, OnInit, ViewChild } from '@angular/core';
import { NgModel, FormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { User } from '../shared/models/user';
declare var Materialize:any;

import { AuthService } from '../shared/services/auth.service';

//TEST
import {Component, Output, EventEmitter} from '@angular/core';
import {FormControl} from '@angular/forms';

import 'rxjs/add/operator/debounceTime';
import 'rxjs/add/operator/distinctUntilChanged';

import {SemanticDomain} from '../shared/models/semantic-domain.model';
import {SemanticDomainDataService} from './sem-dom-data.service';
import {SemanticDomainCollection} from '../shared/models/semantic-domain.model';

@Component({
    moduleId: module.id,
    selector: 'sem-dom-search',
    templateUrl: 'sem-dom-search.component.html'
})
export class SemanticDomainSearchComponent {
    @Output() domainSelected: EventEmitter<SemanticDomain> = new EventEmitter();

    searchText: FormControl = new FormControl();
    searchResults: Array<SemanticDomain> = [];
    semanticDomains: SemanticDomainCollection = {};

    constructor(private semanticDomainDataService: SemanticDomainDataService) {
        this.semanticDomains = semanticDomainDataService.getSemanticDomains();

        this.searchText.valueChanges
            .debounceTime(500)
            .distinctUntilChanged()
            .subscribe(search_text => {
                this.clearSearchResults();

                Object.keys(this.semanticDomains).forEach((key: string) => {
                    let sem_dom: SemanticDomain = this.semanticDomains[key];

                    if (sem_dom.key.indexOf(search_text) >= 0 ||
                        sem_dom.name.indexOf(search_text) >= 0 ||
                        sem_dom.description.indexOf(search_text) >= 0) {
                        this.searchResults.push(sem_dom);
                    }
                });
            });
    }

    // Domain was selected from the select box. Look it up and emit
    // the associated details.
    emitDomain(sem_dom_key: string) {
        this.domainSelected.emit(this.semanticDomains[sem_dom_key]);
        this.clearSearchResults();
    }

    // Clear the search results in preparation for a new query.
    clearSearchResults() {
        // TODO: Is this the right way to do this without leaking memory?
        this.searchResults = [];
    }

    // Should we show the results list?
    showResults() {
        return (this.searchText.value !== "" && this.searchResults.length > 0);
    }
}
//END

@Component({
  moduleId: module.id,
  selector: 'logon',
  templateUrl: 'registerLangSelect.component.html',
})

export class RegisterLangSelectComponent implements OnInit {

  private registerUser: User = new User();
  private authForm: NgModel;
  @ViewChild('registerForm') currentForm: NgModel;

  constructor(
    private router: Router,
    public authService: AuthService
  ) { } 

  ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      this.goToDashboard();
    }
  }

  goToDashboard() {
      this.router.navigate(['dashboard']);
  }

  onSubmit() {

    if(this.registerUser.password == this.registerUser.confPass) {

        var toastContent = '<b>Success! Passwords match</b>';
        Materialize.toast(toastContent, 5000, 'green');

        this.authService.register(this.registerUser.email, this.registerUser.username, this.registerUser.password).subscribe(response => {
            if (response) {
                this.goToDashboard(); //maybe change this to swap you to log in page later
            } else {
                var toastContent = '<b>Error!</b>';
                Materialize.toast(toastContent, 5000, 'red');
              }
            });

    } else {
        var toastContent = '<b>Error! Passwords do NOT match!</b>';
        Materialize.toast(toastContent, 5000, 'red');
      }
    }

}