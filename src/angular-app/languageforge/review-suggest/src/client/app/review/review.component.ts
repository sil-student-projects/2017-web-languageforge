import { Component } from '@angular/core';
import { Config } from '../shared/config/env.config';
import './operators';

/**
 * This class represents the main application component.
 */
@Component({
  moduleId: module.id,
  selector: 'review-component',
  templateUrl: 'review.component.html',
})
export class ReviewComponent {
  constructor() {
    console.log('Environment config', Config);
  }
}
