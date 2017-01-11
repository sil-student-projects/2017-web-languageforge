import { Component } from '@angular/core';
import { Config } from './shared/config/env.config';
import './operators';

/**
 * This class represents the main application component.
 */
@Component({
  moduleId: module.id,
  selector: 'login-component',
  templateUrl: 'login.component.html',
})
export class LoginComponent {
  constructor() {
    console.log('Environment config', Config);
  }
}
