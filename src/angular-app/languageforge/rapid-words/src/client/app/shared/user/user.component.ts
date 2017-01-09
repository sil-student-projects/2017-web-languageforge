import { Component } from '@angular/core';
import { LFApiService } from '../lf-api/lf-api.service';

import { OnInit } from '@angular/core';

@Component({
  moduleId: module.id,
  selector: 'user-component',
  templateUrl: 'user.component.html',
  providers: [
      LFApiService
  ]
})
export class UserComponent implements OnInit {
    ngOnInit(): void {
        console.log('lf api inited')
    }

    constructor(private lfApi: LFApiService) {
        console.log('constructed UserComponent');
    }
}
