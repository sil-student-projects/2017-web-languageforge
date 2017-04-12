import { NgModule } from '@angular/core';

import { ProjectNameFilterPipe } from './project-name-filter.pipe';

@NgModule({
  declarations: [ProjectNameFilterPipe],
  exports : [ProjectNameFilterPipe]
})
export class CustomPipesModule {}