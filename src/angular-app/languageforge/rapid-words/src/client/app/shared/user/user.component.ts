import { Component } from '@angular/core';
import { LfApiService } from '../services/lf-api.service';

import { OnInit } from '@angular/core';

@Component({
  moduleId: module.id,
  selector: 'user-component',
  templateUrl: 'user.component.html',
  providers: [
      LfApiService
  ]
})
export class UserComponent implements OnInit {
    ngOnInit(): void {
        console.log('lf api inited');
        this.performAuthentication();
    }

    constructor(private lfApi: LfApiService) {
        console.log('constructed UserComponent');
    }

    performAuthentication() {
        this.lfApi.performAuthentication();
    }
}
