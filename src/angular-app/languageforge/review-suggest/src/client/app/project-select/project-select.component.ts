import { Component } from '@angular/core';
import { Config } from './shared/config/env.config';
import './operators';

/**
 * This class represents the main application component.
 */
@Component({
  moduleId: module.id,
  selector: 'project-select-component',
  templateUrl: 'project-select.component.html',
})
export class AppComponent {
  constructor() {
    console.log('Environment config', Config);
  }
}
