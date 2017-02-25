import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { DashboardComponent } from './dashboard.component';
import { DashboardRoutingModule } from './dashboard-routing.module';

import { MaterializeModule } from '../shared/materialize.module';
import { CustomPipesModule } from '../shared/pipes/custom-pipes.module';

@NgModule({
  imports: [CommonModule, DashboardRoutingModule, MaterializeModule, FormsModule, CustomPipesModule],
  declarations: [DashboardComponent],
  exports: [DashboardComponent]
})
export class DashboardModule { }